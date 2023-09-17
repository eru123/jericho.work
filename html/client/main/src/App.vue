<script setup>
import useServerData from "@/composables/useServerData";
import { auth_init } from "@/composables/useApi";
import ServerError from "@/views/ServerError.vue";
import Dialogs from "@/components/Dialogs.vue";
import { useRouter } from "vue-router";
import { useWebsocket, on as onWs } from "@/composables/useWebsocket";

const $server = useServerData();
const router = useRouter();

window.__skiddph__redirect = (path) => {
  window.scrollTo(0, 0);
  router.push(path);
};

if ($server?.debug) {
  console.log("Server Data", $server);
  if ($server?.error) {
    console.log("Server Error: ", $server?.error);
  }
}

auth_init("/");
useWebsocket();

onWs("open", () => {
  console.log("Websocket connected");
});

onWs("message", (data) => {
  console.log("Server sent a message", data);
});

onWs("close", (data) => {
  console.log("Websocket closed", data);
  window.__skiddph__retry = true;
  var timeout = 1000;
  var retry = setInterval(() => {
    console.log("Retrying websocket connection");
    if (window.__skiddph__retry) {
      clearInterval(retry);
      window.__skiddph__retry = false;
      timeout *= 2;
      useWebsocket();
    }
  }, 1000);
});

</script>
<template>
  <ServerError v-if="$server?.error" />
  <router-view v-else />
  <Dialogs />
</template>
