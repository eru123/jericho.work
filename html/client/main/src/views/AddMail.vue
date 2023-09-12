<script setup>
import { ref, watch } from "vue";
import PublicPage from "@/components/PublicPage.vue";
import { createError } from "@/composables/useDialog";
import { add_mail, verify_mail, pop_redir } from "@/composables/useApi";

const email = ref("");
const last_tried_email = ref("");
const verification_id = ref(null);
const last_verification_id = ref(null);
const verification_code = ref(null);
const verifying = ref(false);

const enforceRequired = ref(false);
const form = ref(null);

const submit = () => {
  console.log(email.value, verification_id.value, verification_code.value);
  enforceRequired.value = true;

  if (!email.value) {
    return createError("Error", "Please enter your email address.");
  }

  if (!form.value.checkValidity()) {
    return createError("Error", "Please enter a valid email address.");
  }

  if (!verification_id.value) {
    verifying.value = true;
    add_mail(email.value)
      .then((res) => {
        verifying.value = false;
        if (res && res?.success) {
          enforceRequired.value = false;
          verification_id.value = res?.verification_id;
          last_verification_id.value = res?.verification_id;
          last_tried_email.value = email.value;
        }
      })
      .catch((err) => {
        verifying.value = false;
        return createError("Error", err?.message || "An error has occurred.");
      });
  }

  if (verification_id.value) {
    if (!verification_code.value) {
      return createError("Error", "Please enter the verification code.");
    }

    verifying.value = true;
    verify_mail(verification_id.value, verification_code.value)
      .then((res) => {
        verifying.value = false;
        if (res && res?.success) {
          enforceRequired.value = false;
          verification_id.value = null;
          last_verification_id.value = null;
          last_tried_email.value = null;
          return pop_redir("/");
        }

        throw new Error(res?.error || "An error has occurred.");
      })
      .catch((err) => {
        verifying.value = false;
        return createError("Error", err?.message || "An error has occurred.");
      });
  }
};

watch(
  email,
  (n) => {
    console.log(n, last_tried_email.value, n === last_tried_email.value);
    verification_id.value =
      n === last_tried_email.value ? last_verification_id.value : null;
  },
  { immediate: true }
);
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
          />
        </div>
        <div class="form-group" v-if="verification_id">
          <label for="code">Verification Code</label>
          <input
            type="text"
            id="code"
            v-model="verification_code"
            :required="enforceRequired"
            autocomplete="off"
            pattern="[0-9]{6}"
            maxlength="6"
            title="Please enter a valid verification code."
          />
        </div>
        <div class="actions">
          <button type="submit">
            <v-icon
              name="fa-spinner"
              class="icon"
              animation="spin"
              speed="2"
              v-if="verifying"
            ></v-icon>
            <span v-if="!verifying">
              {{ verification_id ? "Verify" : "Send OTP" }}
            </span>
          </button>
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
