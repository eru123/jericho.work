<script setup>
import { ref, computed, watch } from "vue";
import PublicPage from "@/components/PublicPage.vue";
import {
    createInfo,
    createError,
    createConfirm,
    createLoading,
} from "@/composables/useDialog";
import { register } from "@/composables/useApi";

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
            `${fname.value} ${String(mname.value)[0]}. ${lname.value}`,
            `${fname.value} ${mname.value} ${lname.value}`,
            `${fname.value} ${lname.value} ${mname.value}`,
            `${lname.value}, ${fname.value}`,
            `${lname.value}, ${fname.value} ${String(mname.value)[0]}.`,
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
const enforceRequired = ref(false);
const form = ref(null);

const submit = () => {
    const requiredFields = [fname, lname, user, pass, cpass];

    for (const field of requiredFields) {
        if (!field.value) {
            enforceRequired.value = true;
            return createError("Error", "Please fill out all required fields.");
        }
    }

    if (!form.value.checkValidity()) {
        console.log(data);
        return createError("Error", "Please fill out all required fields.");
    }

    if (pass.value !== cpass.value) {
        return createError("Error", "Passwords do not match.");
    }

    const data = {
        fname: fname.value,
        lname: lname.value,
        user: user.value,
        password: pass.value,
        alias: alias.value,
    };

    if (mname.value) {
        data.mname = mname.value;
    }
    const loading = createLoading("Please wait...");
    return createConfirm(
        "Confirmation",
        "Please confirm that all information is correct. Click OK to proceed.",
        (c1) => {
            return register(data)
                .then((res) => {
                    if (res && res?.success) {
                        return createInfo("Success", res.success, (c2) => {
                            window.location.href = "/";
                            c1();
                            c2();
                        });
                    }

                    c1();
                })
                .catch((err) => {
                    return createError(
                        "Error",
                        err?.message || "An error has occurred."
                    );
                })
                .finally(() => {
                    loading.close();
                });
        }
    );
};

watch([fname, mname, lname], () => {
    alias.value = null;
});

</script>
<template>
    <PublicPage>
        <div class="register-container">
            <form class="register" ref="form" @submit.prevent="submit">
                <h1>Register</h1>
                <div class="form-group">
                    <label for="user">Username</label>
                    <input
                        type="text"
                        id="user"
                        v-model="user"
                        :required="enforceRequired"
                        autocomplete="off"
                    />
                </div>
                <div class="form-group">
                    <label for="fname">First Name</label>
                    <input
                        type="text"
                        id="fname"
                        v-model="fname"
                        :required="enforceRequired"
                        autocomplete="off"
                    />
                </div>
                <div class="form-group">
                    <label for="mname"
                        >Middle Name <span>(optional)</span></label
                    >
                    <input
                        type="text"
                        id="mname"
                        v-model="mname"
                        autocomplete="off"
                    />
                </div>
                <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input
                        type="text"
                        id="lname"
                        v-model="lname"
                        :required="enforceRequired"
                        autocomplete="off"
                    />
                </div>
                <div class="form-group" v-if="showAlias">
                    <label for="lname">Display Name</label>
                    <select
                        id="alias"
                        v-model="alias"
                        v-if="showAlias"
                        :required="enforceRequired"
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
                    <input
                        type="password"
                        id="pass"
                        autocomplete="new-password"
                        v-model="pass"
                        :required="enforceRequired"
                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                        title="Must contain at least one  number and one uppercase and lowercase letter, and at least 8 or more characters"
                    />
                </div>
                <div class="form-group">
                    <label for="cpass">Confirm Password</label>
                    <input
                        type="password"
                        id="cpass"
                        autocomplete="new-password"
                        v-model="cpass"
                        :required="enforceRequired"
                        pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                        title="Must contain at least one  number and one uppercase and lowercase letter, and at least 8 or more characters"
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
