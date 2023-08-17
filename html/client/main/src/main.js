import { createApp } from 'vue'
import useServerData from './composables/useServerData'
import router from './router'
import './style.scss'
import App from './App.vue'
import Link from './components/Link.vue'
import { OhVueIcon, addIcons } from "oh-vue-icons";
import {
    BiDiscord,
    BiFacebook,
    BiLinkedin,
    BiGoogle,
    BiEnvelopeFill,
    BiCloudFill,
    FaDatabase,
    MdPolicy,
    MdHandshake,
    BiCheckCircle,
    BiXCircle,
    CoLinkBroken,
    MdAppsoutageTwotone
} from "oh-vue-icons/icons";

addIcons(
    BiDiscord,
    BiFacebook,
    BiLinkedin,
    BiGoogle,
    BiEnvelopeFill,
    BiCloudFill,
    FaDatabase,
    MdPolicy,
    MdHandshake,
    BiCheckCircle,
    BiXCircle,
    CoLinkBroken,
    MdAppsoutageTwotone
);

const app = createApp(App)
app.component("v-icon", OhVueIcon);
app.component('v-link', Link)
app.provide('$server', useServerData())
app.use(router)
app.mount('#app')
