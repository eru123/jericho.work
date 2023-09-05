import useServerData, { BASE_URL } from "./useServerData";
import usePersistentData from "./usePersistentData";
import { createError } from "./useDialog";
export const $server = useServerData();
export const $user = usePersistentData("user", null);

export const post = (url, data) => {
  const url_obj = new URL(url, BASE_URL);
  return fetch(url_obj, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  }).then((res) => res.json());
};

export const get = (url, data) => {
  const url_obj = new URL(url, BASE_URL);
  for (const key in data) {
    url_obj.searchParams.append(key, data[key]);
  }
  return fetch(url_obj).then((res) => res.json());
};

export const loginWithData = (token, data) => {
  $user.value = data;
  $user.value.token = token;
};

export const register = (data) => {
  return post("/api/v1/auth/register", data)
    .then((res) => {
      if (res?.error) {
        throw new Error(res.error);
      }

      if (res?.token && res?.data) {
        loginWithData(res.token, res.data);
        return res;
      }

      throw new Error("Invalid server response");
    })
    .catch((err) => {
      createError("Registration Error", err?.message);
    });
};

export const login = (data) => {
  return post("/api/v1/auth/login", data)
    .then((res) => {
      if (res?.error) {
        throw new Error(res.error);
      }

      if (res?.token && res?.data) {
        loginWithData(res.token, res.data);
        return res;
      }

      throw new Error("Invalid server response");
    })
    .catch((err) => {
      createError("Login Error", err?.message);
    });
};

export const add_mail = (email) => {
  return post("/api/v1/auth/mail/add", { email })
    .then((res) => {
      if (res?.error) {
        throw new Error(res.error);
      }

      if (res?.data) {
        $user.value = res.data;
        return res;
      }

      throw new Error("Invalid server response");
    })
    .catch((err) => {
      createError("Add Mail Error", err?.message);
      return null;
    });
};

export const logout = () => {
  return post("/api/v1/auth/logout", {})
    .then((res) => {
      if (res?.error) {
        throw new Error(res.error);
      }

      return res;
    })
    .catch((err) => {
      createError("Logout Error", err?.message);
    })
    .finally((res) => {
      $user.value = null;
      return res;
    });
};

export default {
  data: $user,
  post,
  get,
};
