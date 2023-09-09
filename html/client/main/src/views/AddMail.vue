<script setup>
import { ref } from "vue";
import PublicPage from "@/components/PublicPage.vue";
import {
  createInfo,
  createError,
  createLoading,
} from "@/composables/useDialog";
import { add_mail } from "@/composables/useApi";

const email = ref("");

const enforceRequired = ref(false);
const form = ref(null);

const submit = () => {
  if (!email.value) {
    enforceRequired.value = true;
    return createError("Error", "Please enter your email address.");
  }

  if (!form.value.checkValidity()) {
    return createError("Error", "Please enter a valid email address.");
  }

  return null;
  //   const loading = createLoading("Please wait...");
  //   return login(data)
  //     .then((res) => {
  //       if (res && res?.success) {
  //         return createInfo("Success", res.success, (c2) => {
  //           window.location.href = "/";
  //           c2();
  //         });
  //       }
  //     })
  //     .catch((err) => {
  //       return createError("Error", err?.message || "An error has occurred.");
  //     })
  //     .finally(() => {
  //       loading.close();
  //     });
};
</script>
<template>
  <PublicPage>
    <div class="add-mail-container">
      <h1>Add new email address</h1>
      <form class="add-mail" ref="form" @submit.prevent="submit">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input
            type="text"
            id="email"
            v-model="email"
            :required="enforceRequired"
            autocomplete="off"
          />
        </div>
        <div class="actions">
          <button type="submit">Add</button>
        </div>
      </form>
    </div>
  </PublicPage>
</template>
<style scoped lang="scss">
.add-mail-container {
  @apply w-full max-w-screen-sm mx-auto min-h-[calc(100vh-3.5rem)] flex flex-col justify-center items-center text-center px-4 py-8;

  & > h1 {
    @apply text-2xl font-semibold text-primary-900 mb-6;
  }
}

.add-mail {
  @apply m-0 md:bg-gray-100 md:p-4 md:rounded-md md:border md:border-gray-400 w-full max-w-[320px];

  .form-group {
    @apply flex flex-col mb-4;

    label {
      @apply text-sm text-gray-600 mb-1 self-start;
      span {
        @apply text-xs text-gray-400;
      }
    }

    input,
    select,
    textarea {
      @apply px-2 py-1 rounded-md border border-gray-400;
      &:invalid {
        @apply border-red-500;
      }

      &:required {
        &::after {
          content: "*";
          @apply text-red-500;
        }
      }
    }
  }

  .actions {
    @apply mt-8;

    button {
      @apply w-full px-4 py-2 rounded-md bg-primary-700 text-white font-semibold hover:bg-primary-800 transition-all duration-200;
    }
  }

  .note {
    @apply text-xs text-gray-500 mt-6;

    a {
      @apply text-primary-700 hover:text-primary-800 transition-all duration-200;
    }
  }

  h1 {
    @apply text-2xl font-semibold text-primary-900 mb-2;
  }
}
</style>
