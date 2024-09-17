<script setup>
import { ref, computed } from "vue";

const search = ref("");
const all_tools = {
  Mail: [
    {
      name: "SMTP Tester",
      icon: "bi-envelope-fill",
      url: "/tools/smtp-tester",
    },
    {
      name: "Mail Catcher",
      icon: "bi-envelope-open-fill",
      url: "/tools/mail-catcher",
    },
  ],
  Cryptography: [
    {
      name: "One way encryption",
      icon: "bi-shield-lock-fill",
      url: "/tools/one-way-encryption",
    },
    {
      name: "Two way encryption",
      icon: "fa-unlock-alt",
      url: "/tools/two-way-encryption",
    },
    {
      name: "JSON Web Token",
      icon: "bi-file-earmark-lock-fill",
      url: "/tools/json-web-token",
    },
  ],
};

const tools = computed(() => {
  if (search.value.length > 0) {
    let filtered_tools = {};
    for (const [category, tools] of Object.entries(all_tools)) {
      filtered_tools[category] = tools.filter((tool) =>
        tool.name.toLowerCase().includes(search.value.toLowerCase())
      );
      if (filtered_tools[category].length === 0) {
        delete filtered_tools[category];
      }
    }
    return filtered_tools;
  } else {
    return all_tools;
  }
});
</script>
<template>
  <v-public-page>
    <div class="container">
      <div class="header">
        <h1>Tools Collection</h1>
        <div class="form-control">
          <label>Search</label>
          <input v-model="search" type="text">
        </div>
      </div>
      <div class="group" v-for="(items, group) of tools" :key="group">
        <h3>{{ group }}</h3>
        <div class="items">
          <v-link
            class="item"
            v-for="item of items"
            :key="item.name"
            :to="item.url"
          >
            <v-icon class="icon" :name="item.icon" />
            <span>{{ item.name }}</span>
          </v-link>
        </div>
      </div>
    </div>
  </v-public-page>
</template>
<style lang="scss" scoped>
.container {
  @apply w-full max-w-screen-xl mx-auto p-4;

  .header {
    @apply flex flex-col md:flex-row md:justify-between md:items-center md:py-4;

    h1 {
      @apply text-2xl mb-4 md:mb-0 text-gray-800;
    }

    .form-control {
      @apply md:w-fit w-full flex items-center py-0 mb-4 md:mb-0;

      label {
        @apply pl-4 pr-2 border-l border-t border-b md:py-1 py-2 rounded-l-md bg-gray-100;
      }

      input {
        @apply flex-1 md:py-1 p-2 border rounded-r;
      }
    }
  }

  .group {
    @apply mb-4;

    h3 {
      @apply text-xl mb-2 text-gray-700;
    }

    .items {
      @apply grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-x-2;

      .item {
        @apply flex items-center p-2 text-primary-800 hover:bg-primary-50 rounded-md;

        .icon {
          @apply mr-4;
        }
      }
    }
  }
}
</style>
