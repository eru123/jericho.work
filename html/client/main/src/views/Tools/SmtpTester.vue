<script setup>
import { ref, computed, watch } from "vue";
import { smtp_tester } from "@/composables/useApi";
import { createError } from "@/composables/useDialog";
import usePersistentData from "@/composables/usePersistentData";

const $smtp_tester = usePersistentData("smtp_tester", null);

const logs = ref("");
const provider = ref($smtp_tester.value?.provider || "smtp");
const from_name = ref($smtp_tester.value?.from_name || "");
const from_email = ref($smtp_tester.value?.from_email || "");
const host = ref($smtp_tester.value?.host || "");
const port = ref($smtp_tester.value?.port || "465");
const secure = ref($smtp_tester.value?.secure || "ssl");
const username = ref($smtp_tester.value?.username || "");
const password = ref($smtp_tester.value?.password || "");
const body = ref($smtp_tester.value?.body || "");
const to = ref($smtp_tester.value?.to || "");
const subject = ref($smtp_tester.value?.subject || "");
const loading = ref(false);

const is_smtp = computed(() => provider.value === "smtp");
const is_gmail = computed(() => provider.value === "gmail");

watch(secure, (value) => {
  if (value === "ssl") {
    port.value = "465";
  } else if (value === "tls") {
    port.value = "587";
  }
});

watch(port, (value) => {
  if (value === "465") {
    secure.value = "ssl";
  } else if (value === "587") {
    secure.value = "tls";
  }
});

watch(from_email, (value) => {
  if (username.value === "") {
    username.value = value;
  }
});

watch(
  () => ({
    provider: provider.value,
    from_name: from_name.value,
    from_email: from_email.value,
    host: host.value,
    port: port.value,
    secure: secure.value,
    username: username.value,
    password: password.value,
    body: body.value,
    to: to.value,
    subject: subject.value,
  }),
  (value) => {
    $smtp_tester.value = value;
  },
  { deep: true }
);

const reset = () => {
  logs.value = "";
  provider.value = "smtp";
  from_name.value = "";
  from_email.value = "";
  host.value = "";
  port.value = "465";
  secure.value = "ssl";
  username.value = "";
  password.value = "";
  body.value = "";
  to.value = "";
  subject.value = "";
};

const send = async () => {
  logs.value = "";
  const data =
    provider.value === "smtp"
      ? {
          provider: provider.value,
          from_name: from_name.value,
          from_email: from_email.value,
          host: host.value,
          port: port.value,
          secure: secure.value,
          username: username.value,
          password: password.value,
          body: body.value,
          to: to.value,
          subject: subject.value,
        }
      : {
          provider: provider.value,
          from_name: from_name.value,
          username: username.value,
          password: password.value,
          body: body.value,
          to: to.value,
          subject: subject.value,
        };
  loading.value = true;
  smtp_tester(data)
    .then((res) => {
      loading.value = false;
      logs.value = res?.logs;
    })
    .catch((error) => {
      loading.value = false;
      createError(error?.response?.error || error?.message || "Unknown error");
    });
};
</script>
<template>
  <v-public-page>
    <div class="container">
      <h1>Tools/SMTP Tester</h1>
      <form @submit.prevent="send">
        <div class="form-inline-groups">
          <div class="form-control md-fit provider">
            <label>Provider</label>
            <select
              name="provider"
              v-model="provider"
              required
              :disabled="loading"
            >
              <option value="smtp">SMTP</option>
              <option value="gmail">Gmail</option>
            </select>
          </div>
          <div class="form-control">
            <label>From name</label>
            <input
              type="text"
              name="from_name"
              required
              :disabled="loading"
              v-model="from_name"
            />
          </div>
          <div class="form-control" v-if="is_smtp">
            <label>From email</label>
            <input
              type="email"
              name="from_email"
              required
              :disabled="loading"
              v-model="from_email"
            />
          </div>
          <div class="form-control" v-if="is_gmail">
            <label>Gmail account</label>
            <input
              type="email"
              name="username"
              v-model="username"
              placeholder="your@gmail.com"
              required
              :disabled="loading"
            />
          </div>
          <div class="form-control" v-if="is_gmail">
            <label>App password</label>
            <input
              type="password"
              name="password"
              v-model="password"
              required
              :disabled="loading"
            />
          </div>
        </div>
        <div class="form-inline-groups" v-if="is_smtp">
          <div class="form-inline-groups">
            <div class="form-control">
              <label>Host</label>
              <input
                type="text"
                name="host"
                required
                :disabled="loading"
                v-model="host"
              />
            </div>
            <div class="form-control md-fit port">
              <label>Port</label>
              <input
                type="text"
                name="port"
                required
                :disabled="loading"
                v-model="port"
              />
            </div>
          </div>
          <div class="form-control md-fit secure">
            <label>Secure</label>
            <select name="secure" required :disabled="loading" v-model="secure">
              <option value="none">None</option>
              <option value="ssl">SSL</option>
              <option value="tls">TLS</option>
            </select>
          </div>
          <div class="form-control">
            <label>Username</label>
            <input
              type="text"
              name="username"
              required
              :disabled="loading"
              v-model="username"
            />
          </div>
          <div class="form-control">
            <label>Password</label>
            <input
              type="password"
              name="password"
              required
              :disabled="loading"
              v-model="password"
            />
          </div>
        </div>
        <div class="form-inline-groups">
          <div class="form-control fit">
            <label>To email</label>
            <input
              type="text"
              name="to"
              required
              :disabled="loading"
              v-model="to"
            />
          </div>
          <div class="form-control">
            <label>Subject</label>
            <input
              type="text"
              name="subject"
              required
              :disabled="loading"
              v-model="subject"
            />
          </div>
        </div>
        <div class="form-control">
          <label>Email body</label>
          <textarea
            name="body"
            rows="3"
            :disabled="loading"
            v-model="body"
          ></textarea>
        </div>
        <div class="actions">
          <v-icon
            name="fa-spinner"
            class="icon"
            animation="spin"
            v-if="loading"
          ></v-icon>
          <button type="button" @click="reset" :disabled="loading">
            Reset
          </button>
          <button type="submit" :disabled="loading">
            {{ loading ? "Sending" : "Send" }}
          </button>
        </div>
      </form>
      <div class="logs-container" v-if="logs">
        <h3>Logs</h3>
        <div class="logs" v-text="logs"></div>
      </div>
    </div>
  </v-public-page>
</template>
<style lang="scss" scoped>
.container {
  @apply w-full max-w-screen-lg mx-auto py-4 px-2;

  & > h1 {
    @apply text-2xl mb-4;
  }

  .form-control {
    @apply w-full mb-2;
    label {
      @apply block pl-1 text-[.8rem] h-5;
    }
    input,
    button,
    select,
    textarea {
      @apply block w-full py-1 px-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm ml-auto mr-0 md:ml-0 md:mr-auto;
    }
  }

  .form-inline-groups {
    @apply flex flex-col sm:flex-row gap-2 md:items-center md:justify-center;

    .form-control {
      @apply flex-[1];

      &.fit {
        @apply flex-[0];

        input,
        button,
        select,
        textarea {
          @apply md:w-fit;
        }
      }

      &.md-fit {
        @apply flex-[1] sm:flex-[0];

        input,
        button,
        select,
        textarea {
          @apply w-full sm:w-fit;
        }
      }

      &.port {
        input,
        button,
        select,
        textarea {
          @apply w-full sm:w-[calc(8ch+1rem)];
        }
      }

      &.secure {
        input,
        button,
        select,
        textarea {
          @apply w-full sm:w-[calc(10ch+1rem)];
        }
      }

      &.provider {
        input,
        button,
        select,
        textarea {
          @apply w-full sm:w-[calc(10ch+1rem)];
        }
      }
    }

    .form-inline-groups {
      @apply flex-row;
    }
  }

  .actions {
    @apply w-full flex flex-row gap-2 mt-2 items-center justify-end;

    button {
      @apply py-1 px-2 sm:text-sm border border-gray-400 hover:border-gray-500 text-gray-900 bg-gray-200 hover:bg-gray-500 hover:text-white rounded-md shadow-sm;
    }
  }

  .logs-container {
    & > h3 {
      @apply text-base ml-1 mb-2;
    }

    .logs {
      @apply w-full max-w-screen-lg bg-gray-100 rounded-md p-2 overflow-x-auto whitespace-pre font-mono text-xs select-text break-words;
    }
  }

  form {
    input,
    textarea,
    select {
      min-width: 100px;
    }
    input,
    select,
    textarea,
    button {
      &:disabled {
        @apply bg-gray-100 border border-gray-400 hover:border-gray-400 hover:bg-gray-100 hover:text-gray-900;
      }
    }
  }
}
</style>
