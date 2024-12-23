<template>
  <button class="login-btn-text">
    <slot></slot>
    <!-- Para permitir que o texto do botão seja personalizado -->
  </button>
</template>

<style css>
.login-btn-text {
  width: 100%;
  height: 50px;
  background: #6db631;
  border-radius: 10px;
  color: white;
  font-size: 17px;
  font-weight: 600;
  text-align: center;
  line-height: 22px;
  cursor: pointer;
  transition: 0.5s;
}

.login-btn-text:hover {
  background-color: #437520; /* Alterando a cor de fundo no hover */
}
</style>
