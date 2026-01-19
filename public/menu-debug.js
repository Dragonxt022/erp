console.log('=== MENU DEBUG ===');

// Intercepta as chamadas axios para /api/menu
const originalGet = axios.get;
axios.get = function(...args) {
    const promise = originalGet.apply(this, args);
    
    if (args[0] === '/api/menu') {
        promise.then(response => {
            console.log('📋 Menu API Response:', response.data);
            
            // Verifica se o item 31 está presente
            let found31 = false;
            response.data.data.forEach(category => {
                category.items.forEach(item => {
                    if (item.id === 31) {
                        found31 = true;
                        console.log('🔴 ITEM 31 ENCONTRADO NO FRONTEND:', item);
                    }
                });
            });
            
            if (!found31) {
                console.log('✅ Item 31 (DRE) NÃO está na resposta da API - filtro funcionando!');
            }
        });
    }
    
    return promise;
};

console.log('=== DEBUG INSTALADO ===');
