
// Intercepta as chamadas axios para /api/menu
const originalGet = axios.get;
axios.get = function(...args) {
    const promise = originalGet.apply(this, args);
    
    if (args[0] === '/api/menu') {
        promise.then(response => {
            
            // Verifica se o item 31 está presente
            let found31 = false;
            response.data.data.forEach(category => {
                category.items.forEach(item => {
                    if (item.id === 31) {
                        found31 = true;
                    }
                });
            });
            
            if (!found31) {
            }
        });
    }
    
    return promise;
};
