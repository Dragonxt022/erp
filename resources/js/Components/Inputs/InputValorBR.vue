<template>
  <input
    :value="valorFormatado"
    @input="atualizarValor($event)"
    class="w-full h-[42.16px] bg-white rounded-lg border-2 border-[#d7d7db] px-2 text-[17px] outline-none text-center"
    placeholder="R$ 0,00"
  />
</template>

<script>
export default {
  props: {
    value: {
      type: String,
      default: '',
    },
  },
  computed: {
    valorFormatado() {
      return this.formatarValorBR(this.value);
    },
  },
  methods: {
    formatarValorBR(valor) {
      let numero = valor.replace(/\D/g, ''); // Remove não-numéricos
      if (!numero) numero = '0';

      const inteiro = numero.slice(0, -2) || '0';
      const centavos = numero.slice(-2);

      return `R$ ${Number(inteiro).toLocaleString('pt-BR')},${centavos}`;
    },
    atualizarValor(event) {
      // Remove tudo que não for número e envia o valor limpo para o pai
      const valor = event.target.value.replace(/\D/g, '');
      this.$emit('input', valor);
    },
  },
};
</script>

<style scoped>
/* Você pode adicionar customizações de estilo aqui, se necessário */
</style>
