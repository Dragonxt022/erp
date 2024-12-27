import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';
import Toast, { useToast } from 'vue-toastification';
import 'vue-toastification/dist/index.css'; // Importa os estilos padrão

import notify from '../Plugins/Notify'; // Caminho para o seu arquivo de notificação

const appName = import.meta.env.VITE_APP_NAME || 'Taiksu';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue')
    ),
  setup({ el, App, props, plugin }) {
    const pinia = createPinia();

    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(Toast, { position: 'top-center', timeout: 3000 })
      .use(notify)
      .use(pinia)
      .use(ZiggyVue)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
