import { createApp } from 'vue';
import axios from 'axios';

import RegisterForm from './components/my_comp/RegisterForm.vue';
import LoginForm from './components/my_comp/LoginForm.vue';
import ResetPasswordForm from './components/my_comp/ResetPasswordForm.vue';
import TaskAdministrationForm from './components/my_comp/TaskAdministrationForm.vue';
import ExecutorAdministrationForm from './components/my_comp/ExecutorAdministrationForm.vue';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
  console.error('CSRF token not found');
}


const app = createApp({});
app.component('register-form', RegisterForm);
app.component('login-form', LoginForm);
app.component('reset-password-form', ResetPasswordForm);
app.component('task-administration-form', TaskAdministrationForm);
app.component('executor-administration-form', ExecutorAdministrationForm);

app.mount('#app');