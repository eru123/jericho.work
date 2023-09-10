import useServerData, { BASE_URL } from "./useServerData";
import usePersistentData from "./usePersistentData";
import { createError } from "./useDialog";
export const $server = useServerData();
export const $user = usePersistentData("user", null);

export const redirect = (to) => window?.__skiddph__redirect(to);

export const post = (url, data) => {
  const url_obj = new URL(url, BASE_URL);
  const headers = {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  };

  if ($user.value?.token) {
    headers.headers["Authorization"] = `Bearer ${$user.value.token}`;
  }

  return fetch(url_obj, headers).then((res) => res.json());
};

export const get = (url, data) => {
  const url_obj = new URL(url, BASE_URL);
  for (const key in data) {
    url_obj.searchParams.append(key, data[key]);
  }

  const headers = {
    method: "GET",
  };

  if ($user.value?.token) {
    headers.headers["Authorization"] = `Bearer ${$user.value.token}`;
  }

  return fetch(url_obj, headers).then((res) => res.json());
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
  return post("/api/v1/auth/logout").then((res) => {
    $user.value = null;
    
    if (res?.error) {
      throw new Error(res.error);
    }
    
    return res;
  });
};

export default {
  data: $user,
  post,
  get,
};
