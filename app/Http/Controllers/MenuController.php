<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MenuController extends Controller
{
    // public function index()
    // {
    //     // Carrega categorias com itens e subitens (parent_id)
    //     $categories = MenuCategory::with(['items' => function($query) {
    //         $query->whereNull('parent_id')->with(['children']);
    //     }])->orderBy('order')->get();

    //     // Converte is_logout em isLogout e aplica recursivamente aos filhos
    //     $categories = $categories->map(function($category) {
    //         $category->items = $category->items->map(function($item) {
    //             // Converte para boolean e força logout se link == 'logout'
    //             $item->isLogout = $item->link === 'logout' ? true : ($item->is_logout == 1);

    //             // Se houver filhos, aplica recursivamente
    //             if ($item->children && $item->children->count()) {
    //                 $item->children = $item->children->map(function($child) {
    //                     $child->isLogout = $child->link === 'logout' ? true : ($child->is_logout == 1);
    //                     return $child;
    //                 });
    //             }

    //             return $item;
    //         });

    //         return $category;
    //     });

    //     // Retorna JSON
    //     return response()->json([
    //         'data' => $categories
    //     ]);
    // }

    /**
     * Verifica se um item deve ser escondido baseado em regras específicas.
     *
     * @param int $itemId
     * @param \App\Models\User $user
     * @return bool
     */

    private function shouldHideItem($itemId, $user): bool
    {
        // 🔴 REGRA ABSOLUTA: Esconde o item 31 (DRE Gerencial) quando o usuário for COLABORADOR E FRANQUEADO
        $isColaborador = (bool) $user->colaborador;
        $isFranqueado = (bool) $user->franqueado;

        // Log detalhado para debug
        Log::info("shouldHideItem - Item: {$itemId}, User: {$user->id}, Colaborador: " . ($isColaborador ? 'SIM' : 'NÃO') . ", Franqueado: " . ($isFranqueado ? 'SIM' : 'NÃO'));

        // Aplica a regra: esconde item 31 se for colaborador E franqueado
        if ($isColaborador && $isFranqueado && $itemId == 31) {
            Log::info("🔴 ITEM 31 (DRE) ESCONDIDO para usuário {$user->id} (colaborador + franqueado)");
            return true;
        }

        return false;
    }

    public function index()
    {
        $user = auth()->user();

        $userRoles = [];
        if ($user->franqueado)   $userRoles[] = 'franqueado';
        if ($user->franqueadora) $userRoles[] = 'franqueadora';
        if ($user->colaborador)  $userRoles[] = 'colaborador';

        $categories = MenuCategory::with([
            'items' => function ($query) {
                $query->whereNull('parent_id')
                    ->with('children')
                    ->orderBy('order');
            }
        ])->orderBy('order')->get();

        $categories = $categories->map(function ($category) use ($user, $userRoles) {

            $category->items = $category->items
                ->filter(function ($item) use ($user, $userRoles) {

                    // 🔴 1. REGRA MANUAL (PRIMEIRO)
                    if ($this->shouldHideItem($item->id, $user)) {
                        return false;
                    }

                    // 🔒 2. required_permission
                    if (!$item->required_permission) {
                        return true;
                    }

                    $permissions = explode('|', $item->required_permission);
                    return count(array_intersect($permissions, $userRoles)) > 0;
                })
                ->map(function ($item) use ($user, $userRoles) {

                    $item->isLogout = $item->link === 'logout'
                        ? true
                        : (bool) $item->is_logout;

                    if ($item->children && $item->children->count()) {
                        $item->children = $item->children
                            ->filter(function ($child) use ($user, $userRoles) {

                                // 🔴 REGRA MANUAL NOS FILHOS
                                if ($this->shouldHideItem($child->id, $user)) {
                                    return false;
                                }

                                if (!$child->required_permission) {
                                    return true;
                                }

                                $permissions = explode('|', $child->required_permission);
                                return count(array_intersect($permissions, $userRoles)) > 0;
                            })
                            ->values();
                    }

                    return $item;
                })
                ->values(); // 🔴 IMPORTANTE: Reindexar array após filtro

            return $category;
        });

        // 🔍 DEBUG: Log dos IDs de itens que serão retornados
        $itemIds = [];
        foreach ($categories as $category) {
            foreach ($category->items as $item) {
                $itemIds[] = $item->id;
            }
        }
        Log::info("📋 IDs de itens na resposta JSON: " . implode(', ', $itemIds));

        return response()->json(['data' => $categories]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:menu_categories,id',
            'parent_id' => 'nullable|exists:menu_items,id',
            'label' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'is_logout' => 'boolean',
            'required_permission' => 'nullable|string|max:255',
            'order' => 'integer',
        ]);

        $menuItem = MenuItem::create($validated);

        return response()->json(['data' => $menuItem], 201);
    }

    public function reorder(Request $request)
    {
        $items = $request->input('items');

        if (!is_array($items)) {
            return response()->json(['error' => 'Dados inválidos'], 422);
        }

        // Validação básica dos payloads
        foreach ($items as $i => $it) {
            if (!isset($it['id']) || !isset($it['order'])) {
                return response()->json(['error' => "Item inválido na posição {$i}"], 422);
            }
        }

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $update = ['order' => (int) $item['order']];
                // Se veio category_id (quando arrasta entre categorias), atualiza também
                if (isset($item['category_id'])) {
                    $update['category_id'] = $item['category_id'];
                }
                MenuItem::where('id', $item['id'])->update($update);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar nova ordem',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:menu_categories,id',
            'parent_id' => 'nullable|exists:menu_items,id',
            'label' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'link' => 'nullable|string|max:255',
            'is_logout' => 'boolean',
            'required_permission' => 'nullable|string|max:255',
            'order' => 'integer',
        ]);

        $menuItem = MenuItem::findOrFail($id);
        $menuItem->update($validated);

        return response()->json(['data' => $menuItem]);
    }

    public function destroy($id)
    {
        $menuItem = MenuItem::findOrFail($id);
        $menuItem->delete();

        return response()->json(['success' => true]);
    }
}
