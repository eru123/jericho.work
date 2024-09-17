<script setup>
import { ref } from "vue";
import { createError, createInfo } from "@/composables/useDialog";
import { newsletter_add } from "@/composables/useApi";

const email = ref("");
const subscribing = ref(false);
const subscribe = () => {
  subscribing.value = true;
  newsletter_add({
    email: email.value,
  })
    .then((res) => {
      if (res && res?.success) {
        email.value = "";
        return createInfo("Success", res?.success);
      }

      throw new Error(res?.error || "An error has occurred.");
    })
    .catch((err) => {
      createError("Error", err?.message || "An error has occurred.");
    })
    .finally(() => {
      subscribing.value = false;
    });
};
</script>
<template>
  <div class="newsletter-card">
    <div class="newsletter-title">
      <h2>Subscribe to our newsletter</h2>
    </div>
    <div class="newsletter-form">
      <form v-on:submit.prevent="subscribe">
        <input
          type="email"
          placeholder="Enter your email address"
          v-model="email"
          required
        />
        <button type="submit">
          <v-icon
            name="fa-spinner"
            class="icon"
            animation="spin"
            v-if="subscribing"
          ></v-icon>
          <span v-if="!subscribing">Subscribe</span>
        </button>
      </form>
    </div>
  </div>
</template>
<style lang="scss" scoped>
.newsletter-card {
  @apply w-full bg-white rounded-md shadow-md overflow-hidden;

  .newsletter-title {
    @apply w-full bg-primary-900 text-white py-4 px-4;

    h2 {
      @apply text-xl sm:text-2xl font-semibold;
    }
  }

  .newsletter-form {
    @apply w-full bg-white py-8 px-4;

    form {
      @apply flex flex-col sm:flex-row sm:justify-center sm:items-center m-0;

      input {
        @apply w-full text-base sm:text-sm sm:w-96 px-4 py-2 border border-gray-300 rounded-md mb-4 sm:mb-0 sm:mr-4;
      }

      button {
        @apply px-4 py-2 rounded-md text-base sm:text-sm font-normal text-primary-50 bg-primary-900 hover:bg-primary-800;
      }
    }
  }
}
</style>
