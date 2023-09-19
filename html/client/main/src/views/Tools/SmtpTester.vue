<template>
  <v-public-page>
    <div class="container">
      <h1>Tools/SMTP Tester</h1>
      <form>
        <div class="form-inline-groups">
          <div class="form-control md-fit provider">
            <label>Provider</label>
            <select name="provider">
              <option value="custom">Custom</option>
              <option value="gmail">Gmail</option>
            </select>
          </div>
          <div class="form-inline-groups">
            <div class="form-control">
              <label>Host</label>
              <input type="text" name="host" />
            </div>
            <div class="form-control md-fit port">
              <label>Port</label>
              <input type="text" name="port" />
            </div>
          </div>
          <div class="form-control md-fit secure">
            <label>Secure</label>
            <select name="secure">
              <option value="none">None</option>
              <option value="ssl">SSL</option>
              <option value="tls">TLS</option>
            </select>
          </div>
          <div class="form-control">
            <label>Username</label>
            <input type="text" name="username" />
          </div>
          <div class="form-control">
            <label>Password</label>
            <input type="text" name="password" />
          </div>
        </div>
        <div class="form-control">
          <label>Email body</label>
          <textarea name="body" rows="3"></textarea>
        </div>
        <div class="actions">
          <div class="form-control">
            <label></label>
            <button type="submit">Send</button>
          </div>
        </div>
      </form>
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
        @apply flex-row ;
    }
  }
}
</style>
