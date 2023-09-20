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
    }
  ],
};

const tools = computed(() => {
  if (search.value.length > 0) {
    let filtered_tools = {};
    for (const [category, tools] of Object.entries(all_tools)) {
      filtered_tools[category] = tools.filter((tool) =>
        tool.name.toLowerCase().includes(search.value.toLowerCase())
      );
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
      <h1>Tools Collection</h1>
      <div class="row">
        <div class="col-12 col-md-6">
          <v-input v-model="search" placeholder="Search"></v-input>
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

  h1 {
    @apply text-3xl mb-4 text-gray-800;
  }

  .group {
    @apply mb-4;

    h3 {
      @apply text-2xl mb-2 text-gray-700;
    }

    .items {
      @apply grid grid-cols-1 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-x-2;

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
