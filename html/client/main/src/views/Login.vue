<script setup>
import { ref } from "vue";
import PublicPage from "@/components/PublicPage.vue";
import { createError } from "@/composables/useDialog";
import { login, redirect } from "@/composables/useApi";

const user = ref("");
const pass = ref("");

const enforceRequired = ref(false);
const form = ref(null);
const loggingIn = ref(false);

const submit = () => {
  const requiredFields = [user, pass];

  for (const field of requiredFields) {
    if (!field.value) {
      enforceRequired.value = true;
      return createError("Error", "Please fill out all required fields.");
    }
  }

  if (!form.value.checkValidity()) {
    return createError("Error", "Please fill out all required fields.");
  }

  const data = {
    user: user.value,
    password: pass.value,
  };

  loggingIn.value = true;
  return login(data)
    .then((res) => {
      if (res && res?.success) {
        redirect("/");
      }
    })
    .catch((err) => {
      return createError("Error", err?.message || "An error has occurred.");
    })
    .finally(() => {
      loggingIn.value = false;
    });
};
</script>
<template>
  <PublicPage>
    <div class="login-container">
      <form class="login" ref="form" @submit.prevent="submit">
        <h1>Login</h1>
        <div class="form-group">
          <label for="user">Username</label>
          <input
            type="text"
            id="user"
            v-model="user"
            :required="enforceRequired"
          />
        </div>
        <div class="form-group">
          <label for="pass">Password</label>
          <input
            type="password"
            id="pass"
            v-model="pass"
            :required="enforceRequired"
          />
        </div>
        <div class="note">
          By registering an account, you have agreed to our
          <v-link target="_blank" to="/terms-and-conditions"
            >Terms and Conditions</v-link
          >, and that you have read our
          <v-link target="_blank" to="/privacy-policy">Privacy Policy</v-link>.
        </div>
        <div class="actions">
          <button type="submit">
            <v-icon
              name="fa-spinner"
              class="icon"
              animation="spin"
              speed="2"
              v-if="loggingIn"
            ></v-icon>
            <span v-if="!loggingIn">Login</span>
          </button>
        </div>
      </form>
    </div>
  </PublicPage>
</template>
<style scoped lang="scss">
.login-container {
  @apply w-full max-w-screen-sm mx-auto min-h-[calc(100vh-3.5rem)] flex flex-col justify-center items-center text-center px-4 py-8;
}

.login {
  @apply m-0 sm:bg-gray-100 sm:p-4 sm:rounded-md sm:border sm:border-gray-400 w-full max-w-[320px];

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
