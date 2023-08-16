<script setup>
import LogoWD from "@/assets/logo-w-dark.svg";
import useServerData from "@/composables/useServerData";

const server = useServerData();
const year = new Date().getFullYear();
const items = [
    {
        title: "About Us",
        links: [
            {
                name: "Email us",
                url: "mailto:hello@skiddph.com",
                icon: "bi-envelope-fill",
            },
            {
                name: "Privacy Policy",
                url: "/privacy-policy",
                icon: "md-policy",
            },
            {
                name: "Terms and Conditions",
                url: "/terms-and-conditions",
                icon: "md-handshake",
            },
            {
                name: "Google Reviews",
                url: "https://g.page/r/CVx7yccU-v3cEAI/review",
                icon: "bi-google",
            },
        ],
    },
    {
        title: "Social",
        links: [
            {
                name: "Facebook",
                url: "https://facebook.com/skiddph",
                icon: "bi-facebook",
            },
            {
                name: "LinkedIn",
                url: "https://www.linkedin.com/company/skiddph",
                icon: "bi-linkedin",
            },
            {
                name: "Discord",
                url: "https://discord.gg/ugSanJu5BJ",
                icon: "bi-discord",
            },
        ],
    },
    {
        title: "Free Services",
        links: [
            {
                name: "OpenCDN",
                url: (server?.BASE_URL ?? "") + "/cdn",
                icon: "bi-cloud-fill",
            },
            {
                name: "e-Kartero",
                url: "https://skiddph.com",
                icon: "bi-envelope-fill",
            },
            {
                name: "Dev MySQL",
                url: "https://skiddph.com",
                icon: "fa-database",
            },
        ],
    },
];
</script>
<template>
    <footer>
        <div>
            <img :src="LogoWD" alt="Logo" />
            <h4>Copyright &copy; {{ year }} SKIDD PH</h4>
        </div>
        <div>
            <div v-for="item in items" :key="item.title">
                <h3>{{ item.title }}</h3>
                <ul>
                    <li v-for="link in item.links" :key="link.name">
                        <v-link
                            :to="link.url"
                            rel="noopener"
                            class="footer-link"
                        >
                            <v-icon :name="link.icon" />
                            {{ link.name }}
                        </v-link>
                    </li>
                </ul>
            </div>
        </div>
    </footer>
</template>
<style lang="scss" scoped>
footer {
    @apply bg-primary-900 text-primary-50 flex justify-center items-center flex-col py-4;

    & > div:first-child {
        @apply flex flex-col justify-start items-center mt-4 mb-8 w-full max-w-screen-lg mx-auto px-4;

        img {
            @apply w-auto h-[2.3rem] mr-2 my-[.1rem];
        }

        h4 {
            @apply text-xs font-normal text-primary-100;
        }
    }

    & > div:last-child {
        @apply grid grid-cols-2 md:grid-cols-3 gap-4 w-full max-w-screen-md mx-auto px-4 py-2;

        div {
            @apply flex flex-col justify-start items-start;
        }

        h3 {
            @apply text-lg font-semibold text-primary-50 mb-2 px-2;
        }

        ul {
            @apply flex flex-col;

            li {
                .footer-link {
                    @apply flex items-center text-sm font-normal text-primary-50 hover:bg-primary-800 px-2 py-1;

                    .ov-icon {
                        @apply mr-2;
                    }
                }
            }
        }
    }
}
</style>
