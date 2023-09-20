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
import { OhVueIcon, addIcons } from "oh-vue-icons";
import {
  BiDiscord,
  BiFacebook,
  BiLinkedin,
  BiGoogle,
  BiEnvelopeFill,
  BiEnvelopeOpenFill,
  BiCloudFill,
  FaDatabase,
  MdPolicy,
  MdHandshake,
  BiCheckCircle,
  BiXCircle,
  CoLinkBroken,
  MdAppsoutageTwotone,
  IoLogoAppleAr,
  BiInfoCircle,
  MdDangerousOutlined,
  CoWarning,
  BiQuestionCircle,
  BiHourglassBottom,
  BiHourglassSplit,
  HiSolidUserCircle,
  FaSpinner,
  BiThreeDots,
  BiShieldLockFill,
  FaUnlockAlt,
  BiFileEarmarkLockFill 
  // BiHourglassTop,
  // BiHourglass,
} from "oh-vue-icons/icons";

addIcons(
  BiDiscord,
  BiFacebook,
  BiLinkedin,
  BiGoogle,
  BiEnvelopeFill,
  BiEnvelopeOpenFill,
  BiCloudFill,
  FaDatabase,
  MdPolicy,
  MdHandshake,
  BiCheckCircle,
  BiXCircle,
  CoLinkBroken,
  MdAppsoutageTwotone,
  IoLogoAppleAr,
  BiInfoCircle,
  MdDangerousOutlined,
  CoWarning,
  BiQuestionCircle,
  BiHourglassBottom,
  BiHourglassSplit,
  HiSolidUserCircle,
  FaSpinner,
  BiThreeDots,
  BiShieldLockFill,
  FaUnlockAlt,
  BiFileEarmarkLockFill
  // BiHourglassTop,
  // BiHourglass,
);

const app = createApp(App);
app.component("v-icon", OhVueIcon);
app.component("v-link", Link);
app.component("v-public-page", PublicPage);
app.component("v-public-header", PublicHeader);
app.component("v-public-footer", PublicFooter);
app.provide("$server", useServerData());
app.use(router);
app.mount("#app");
