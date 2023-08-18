<script setup>
import { ref, computed, watch } from "vue";
import PublicPage from "@/components/PublicPage.vue";

const fname = ref("");
const mname = ref("");
const lname = ref("");
const user = ref("");
const pass = ref("");
const cpass = ref("");

const aliases = computed(() => {
    if (fname.value && mname.value && lname.value) {
        return [
            `${fname.value} ${lname.value}`,
            `${fname.value} ${mname.value} ${lname.value}`,
            `${fname.value} ${lname.value} ${mname.value}`,
            `${lname.value}, ${fname.value}`,
            `${lname.value}, ${fname.value} ${mname.value}`,
        ];
    } else if (fname.value && lname.value) {
        return [
            `${fname.value} ${lname.value}`,
            `${lname.value} ${fname.value}`,
            `${lname.value}, ${fname.value}`,
        ];
    }

    return [];
});
const showAlias = computed(
    () =>
        (fname.value && lname.value) ||
        (fname.value && mname.value && lname.value)
);
const alias = ref(null);

const form = ref(null);

const submit = () => {
    const data = {
        fname: fname.value,
        mname: mname.value,
        lname: lname.value,
        user: user.value,
        pass: pass.value,
        alias: alias.value,
    };

    if (form.value.checkValidity()) {
        console.log(data);
    }
};
</script>
<template>
    <PublicPage>
        <div class="register-container">
            <form class="register" ref="form" @submit.prevent="submit">
                <h1>Register</h1>
                <div class="form-group">
                    <label for="user">Username</label>
                    <input type="text" id="user" v-model="user" required />
                </div>
                <div class="form-group">
                    <label for="fname">First Name</label>
                    <input type="text" id="fname" v-model="fname" required />
                </div>
                <div class="form-group">
                    <label for="mname"
                        >Middle Name <span>(optional)</span></label
                    >
                    <input type="text" id="mname" v-model="mname" />
                </div>
                <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input type="text" id="lname" v-model="lname" required />
                </div>
                <div class="form-group" v-if="showAlias">
                    <label for="lname">Display Name</label>
                    <select
                        id="alias"
                        v-model="alias"
                        v-if="showAlias"
                        required
                    >
                        <option
                            v-for="a in aliases"
                            :key="a"
                            :value="a"
                            :selected="a === alias"
                        >
                            {{ a }}
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pass">Password</label>
                    <input type="password" id="pass" v-model="pass" required />
                </div>
                <div class="form-group">
                    <label for="cpass">Confirm Password</label>
                    <input
                        type="password"
                        id="cpass"
                        v-model="cpass"
                        required
                    />
                </div>
                <div class="note">
                    By registering an account, you ahve agreed to our
                    <v-link to="/terms-and-conditions"
                        >Terms and Conditions</v-link
                    >, and that you have read our
                    <v-link to="/privacy-policy">Privacy Policy</v-link>.
                </div>
                <div class="actions">
                    <button type="submit">Register</button>
                </div>
            </form>
        </div>
    </PublicPage>
</template>
<style scoped lang="scss">
.register-container {
    @apply w-full max-w-screen-sm mx-auto min-h-[calc(100vh-3.5rem)] flex flex-col justify-center items-center text-center px-4 py-8;
}

.register {
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
