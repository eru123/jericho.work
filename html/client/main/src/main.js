import { createApp } from 'vue'
import router from './router'
import './style.scss'
import App from './App.vue'
import Link from './components/Link.vue'
import { OhVueIcon, addIcons } from "oh-vue-icons";
import {
    BiDiscord, 
    BiFacebook,
    BiLinkedin,
    BiEnvelopeFill,
    BiCloudFill,
    FaDatabase,
    MdPolicy,
    MdHandshake
} from "oh-vue-icons/icons";

addIcons(
    BiDiscord,
    BiFacebook,
    BiLinkedin,
    BiEnvelopeFill,
    BiCloudFill,
    FaDatabase,
    MdPolicy,
    MdHandshake
);

const app = createApp(App)
app.component("v-icon", OhVueIcon);
app.component('v-link', Link)
app.use(router)
app.mount('#app')
