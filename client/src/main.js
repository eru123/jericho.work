import "core-js/full/set/intersection";
import 'vite/modulepreload-polyfill'
import { createApp } from "vue";
import useServerData from "./composables/useServerData";
import router from "./router";
import "./style.scss";
import App from "./App.vue";
import Link from "./components/Link.vue";
import PublicPage from "./components/PublicPage.vue";
import PublicHeader from "./components/PublicHeader.vue";
import PublicFooter from "./components/PublicFooter.vue";
import FixedLayout from "./components/FixedLayout.vue";
import GlobalTable from "./components/GlobalTable.vue";
import { OhVueIcon, addIcons } from "oh-vue-icons";
import * as icons from "./icons";

addIcons(...Object.values(icons));

const app = createApp(App);
app.component("v-icon", OhVueIcon);
app.component("v-link", Link);
app.component("v-public-page", PublicPage);
app.component("v-public-header", PublicHeader);
app.component("v-public-footer", PublicFooter);
app.component("v-fixed-layout", FixedLayout);
app.component("v-global-table", GlobalTable);
app.use(router);
app.mount("#app");
