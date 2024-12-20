<template>
    <div
        class="min-h-screen flex items-center justify-center bg-gradient-to-r from-blue-400 to-indigo-600 text-white"
    >
        <div class="text-center">
            <div class="animate-bounce mb-6">
                <span class="text-6xl font-bold">🚧</span>
            </div>
            <h1 class="text-4xl font-extrabold mb-4">Estamos em Construção!</h1>
            <p class="text-lg mb-6">
                Nosso site está sendo atualizado. Volte em breve!
            </p>
            <div class="text-sm text-gray-200">
                Enquanto isso, você pode ficar conectado conosco nas redes
                sociais.
            </div>
            <div class="mt-6">
                <a href="https://www.taiksu.com.br/" target="_blank">
                    <button
                        class="px-6 py-3 bg-yellow-400 hover:bg-yellow-500 rounded-lg text-black font-semibold transition duration-300"
                    >
                        Acesse a pagina oficial.
                    </button>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "ConstructionPage",
};
</script>

<style scoped>
/* Adicionando uma animação personalizada para o ícone de construção */
@keyframes bounce {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}
</style>
