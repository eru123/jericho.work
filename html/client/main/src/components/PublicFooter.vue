<script setup>
import LogoWD from "@/assets/logo-w-dark.svg";

const year = new Date().getFullYear();
const items = [
  {
    title: "Legal",
    links: [
      {
        name: "Email us",
        url: "mailto:hello@skiddph.com",
        icon: "bi-envelope-fill",
        _blank: true,
      },
      {
        name: "Privacy Policy",
        url: "/privacy-policy",
        icon: "md-policy",
      },
      {
        name: "T&C",
        url: "/terms-and-conditions",
        icon: "md-handshake",
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
        _blank: true,
      },
      {
        name: "LinkedIn",
        url: "https://www.linkedin.com/company/skiddph",
        icon: "bi-linkedin",
        _blank: true,
      },
      {
        name: "Discord",
        url: "https://discord.gg/ugSanJu5BJ",
        icon: "bi-discord",
        _blank: true,
      },
    ],
  },
  {
    title: "Services",
    links: [
      {
        name: "OpenCDN",
        url: "/cdn",
        icon: "bi-cloud-fill",
        _blank: true,
      },
      {
        name: "e-Kartero",
        url: "/feature-not-available",
        icon: "bi-envelope-fill",
      },
      {
        name: "imbakDB",
        url: "/feature-not-available",
        icon: "fa-database",
      },
    ],
  },
  {
    title: "Reviews",
    links: [
      {
        name: "Google",
        url: "https://g.page/r/CVx7yccU-v3cEAI/review",
        icon: "bi-google",
        _blank: true,
      },
      {
        name: "Facebook",
        url: "https://facebook.com/skiddph/reviews",
        icon: "bi-facebook",
        _blank: true,
      },
    ],
  },
  {
    title: "Tools",
    links: [
      {
        name: "SMTP Tester",
        url: "/tools/smtp-tester",
        icon: "bi-envelope-fill",
      },
      {
        name: "Mail Catcher",
        url: "/tools/mail-catcher",
        icon: "bi-envelope-open-fill",
      },
      {
        name: "See more",
        url: "/tools",
        icon: "bi-three-dots",
      }
    ]
  }
];
</script>
<template>
  <footer v-once>
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
              :target="link?._blank ? '_blank' : undefined"
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
    @apply flex flex-col justify-start items-center mt-4 mb-8 w-full max-w-screen-xl mx-auto px-4;

    img {
      @apply w-auto h-[2.3rem] mr-2 my-[.1rem];
    }

    h4 {
      @apply text-xs font-normal text-primary-100;
    }
  }

  & > div:last-child {
    @apply grid grid-cols-2 md:grid-cols-5 gap-4 w-full max-w-screen-md mx-auto px-4 py-2;

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
