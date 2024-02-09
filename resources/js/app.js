import { createApp } from 'vue';
import MailWeb from './Pages/MailWeb.vue';

const app = createApp({
    components: {
        MailWeb
    }
});

app.mount('#app');
