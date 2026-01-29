<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MenuController extends Controller
{

    /**
     * Verifica se um item deve ser escondido baseado em regras específicas.
     *
     * @param int $itemId
     * @param \App\Models\User $user
     * @return bool
     */

    private function shouldHideItem($itemId, $user): bool
    {
        $isColaborador = (bool) $user->colaborador;
        $isFranqueado = (bool) $user->franqueado;

        // Aplica a regra: esconde itens se for colaborador E franqueado
        if ($isColaborador && $isFranqueado && $itemId == 31) {
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

                    if ($this->shouldHideItem($item->id, $user)) {
                        return false;
                    }

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
                ->values();

            return $category;
        });

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
