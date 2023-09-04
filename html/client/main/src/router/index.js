import { createRouter, createWebHistory } from "vue-router";
import Landing from "@/views/Landing.vue";

import MDLayout from "@/layouts/PublicMarkdown.vue";
import MDPrivacyPolicy from "@/md/privacy-policy.md";
import MDTermsAndConditions from "@/md/terms-and-conditions.md";
import AddMail from "@/views/AddMail.vue";
import Verify from "@/views/Verify.vue";
import NotFound from "@/views/NotFound.vue";
import FeatureNotAvailable from "@/views/FeatureNotAvailable.vue";
import Register from "@/views/Register.vue";
import Login from "@/views/Login.vue";

import usePersistentData from "@/composables/usePersistentData";
const userData = usePersistentData("user", null);

const routes = [
  {
    path: "/",
    name: "Landing",
    component: Landing,
    beforeEnter: (to, from, next) => {
      if (userData?.value?.token) {
        if (!userData?.value?.email_verified) {
            next("/add-mail");
        }

        next("/dashboard");
      }
    },
  },
  {
    path: "/",
    name: "Markdown",
    component: MDLayout,
    children: [
      {
        path: "privacy-policy",
        name: "PrivacyPolicy",
        component: MDPrivacyPolicy,
      },
      {
        path: "terms-and-conditions",
        name: "TermsAndConditions",
        component: MDTermsAndConditions,
      },
    ],
  },
  {
    path: "/verify/:token",
    name: "Verify",
    component: Verify,
  },
  {
    path: "/add-mail",
    name: "AddMail",
    component: AddMail,
  },
  {
    path: "/register",
    name: "Register",
    component: Register,
    beforeEnter: (to, from, next) => {
      if (userData?.value?.token) {
        next("/");
      }
    },
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
    beforeEnter: (to, from, next) => {
      if (userData?.value?.token) {
        next("/");
      }
    },
  },
  {
    path: "/feature-not-available",
    name: "FeatureNotAvailable",
    component: FeatureNotAvailable,
  },
  {
    path: "/:pathMatch(.*)*",
    component: NotFound,
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export const paths = routes
  .reduce(
    (all, cur) =>
      cur?.children
        ? all.concat(
            cur.children.map((c) =>
              c.path.charAt(0) === "/" ? c.path : cur.path + c.path
            )
          )
        : all.concat(cur.path.charAt(0) === "/" ? cur.path : `/${cur.path}`),
    []
  )
  .map((p) => p.toLowerCase());
export default router;
