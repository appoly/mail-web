import { createApp } from 'vue';
import MailwebDashboard from './MailwebDashboard.vue';

import '../css/mailweb.css';

const app = createApp(MailwebDashboard, {
    debug: true,
});
app.mount('#app');

app.config.errorHandler = (err, vm, info) => {
  console.error('Vue error:', err, info);
};