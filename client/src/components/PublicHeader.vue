<script setup>
import { computed } from "vue";
import LogoWD from "@/assets/logo-w-dark.svg";
import LogoD from "@/assets/logo-dark.svg";
import { usePersistentData } from "@/composables/usePersistentData";
import { logout, redirect } from "@/composables/useApi";
import { createConfirm } from "@/composables/useDialog";

const user = usePersistentData("user", null);
const authed = computed(() => user.value?.token);

const confirmLogout = () => {
  createConfirm("Logout", "Are you sure you want to logout?", (c1) => {
    c1();
    logout()
      .then(() => redirect("/"))
      .catch(() => redirect("/"));
  });
};
</script>
<template>
  <header>
    <nav>
      <v-link to="/" class="brand">
        <img :src="LogoWD" alt="Logo" />
        <img :src="LogoD" alt="Logo" />
      </v-link>
      <div class="actions">
        <v-link class="desktop-only" v-if="!authed" to="/"> Home </v-link>
        <v-link class="desktop-only" v-if="!authed" to="/services"> Services </v-link>
        <v-link class="desktop-only" v-if="!authed" to="/about"> About </v-link>
        <v-link class="desktop-only" v-if="!authed" to="/login"> Login </v-link>
        <v-link class="desktop-only" v-if="!authed" to="/contact"> Contact Us </v-link>
        <a class="dropdown mobile-only" v-if="!authed">
          <v-icon name="hi-solid-menu-alt-3" class="icon"></v-icon>
          <div class="items">
            <v-link v-if="!authed" to="/"> Home </v-link>
            <v-link v-if="!authed" to="/services"> Services </v-link>
            <v-link v-if="!authed" to="/about"> About </v-link>
            <v-link v-if="!authed" to="/login"> Login </v-link>
            <v-link v-if="!authed" to="/contact"> Contact Us </v-link>
          </div>
        </a>
        <a class="dropdown" v-if="authed">
          <v-icon name="hi-solid-user-circle" class="icon mr-2"></v-icon>
          <span class="shortname">
            {{ user?.fname ?? user?.user }}
          </span>
          <span class="longname">
            {{ user?.name ?? user?.fname ?? user?.user }}
          </span>
          <div class="items">
            <v-link to="/profile">Profile</v-link>
            <button @click="confirmLogout">Logout</button>
          </div>
        </a>
      </div>
    </nav>
  </header>
</template>
<style scoped lang="scss">
header {
  @apply bg-primary-900 text-primary-50 flex justify-center sticky top-0 z-50;

  nav {
    @apply mx-auto flex justify-between items-center w-full px-4 py-2;

    .brand {
      @apply flex items-center;

      img:first-child {
        @apply w-auto h-[2.3rem] mr-2 my-[.1rem] hidden md:block;
      }

      img:last-child {
        @apply w-auto h-[2.5rem] mr-2 block md:hidden;
      }
    }

    .actions {
      @apply flex items-center;

      &>a {
        @apply px-4 py-2 rounded-md text-sm font-normal text-primary-50 hover:bg-primary-800;
        @apply flex items-center;

        &.dropdown {
          @apply relative;

          svg.icon {
            @apply w-6 h-6;
          }

          .shortname {
            @apply block md:hidden;
          }

          .longname {
            @apply hidden md:block;
          }

          .items {
            @apply absolute top-full right-0 w-48 bg-primary-900 rounded-md shadow-lg py-1 z-10;
            @apply hidden;

            &>* {
              @apply text-left w-full block px-4 py-2 text-sm text-primary-50 hover:bg-primary-800 transition-all duration-200 rounded-md;
            }
          }

          &:hover {
            .items {
              @apply block;
              animation: dropdown 200ms ease-in-out forwards;
            }
          }
        }
      }

      .mobile-only {
        @apply inline-block md:hidden;
      }

      .desktop-only {
        @apply hidden md:inline-block;
      }
    }
  }
}

@keyframes dropdown {
  0% {
    opacity: 0;
    transform: translateY(-50%) translateX(50%) scale(0);
  }

  100% {
    opacity: 1;
    transform: translateY(0) translateX(0) scale(1);
  }
}
</style>
