import useServerData, { BASE_URL } from "./useServerData";
import usePersistentData from "./usePersistentData";
import { createError } from "./useDialog";
// import { restart as restartWs, on as onWs, close as closeWs } from "./useWebsocket";

// onWs("open", () => {
//   console.log("Websocket connected");
// });

// onWs("message", (data) => {
//   console.log("Server sent a message", data);
// });


// onWs("close", (data) => {
//   console.log("Websocket closed", data);
//   window.__skiddph__retry = true;
//   var retry = setInterval(async () => {
//     console.log("Retrying websocket connection");
//     if (window?.__skiddph__retry) {
//       clearInterval(retry);
//       delete window.__skiddph__retry;
//       restartWs();
//     }
//   }, 5000);
// });

export const $server = useServerData();
export const $user = usePersistentData("user", null);
export const $data = usePersistentData("data", null);
export const $redir = usePersistentData("redir", null);
export const $reports = usePersistentData("reports", null);

export const redirect = (to) => window?.__skiddph__redirect(to);

export const add_redir = (to) => {
  if (!$redir.value) {
    $redir.value = [];
  }

  $redir.value.push(to);
};

export const pop_redir = (fallback = '/') => {
  if (!$redir.value) {
    return redirect(fallback);
  }

  const to = $redir.value?.pop();
  if (!$redir.value.length) {
    $redir.value = null;
  }
  return redirect(to ? to : fallback);
};


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
    headers.headers["X-Last-Data-Update"] = $user.value?.last_data_update || Date.now();
    headers.headers["X-Last-Token-Update"] = $user.value?.last_token_update || Date.now();
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
    headers.headers["X-Last-Data-Update"] = $user.value?.last_data_update || Date.now();
    headers.headers["X-Last-Token-Update"] = $user.value?.last_token_update || Date.now();
  }

  return fetch(url_obj, headers).then((res) => res.json());
};

export const loginWithData = (token, data) => {
  const is_token_refreshed = $user.value?.token === token;
  $user.value = data;
  $user.value.token = token;
  $user.value.last_data_update = Date.now();
  if (is_token_refreshed) {
    $user.value.last_token_update = Date.now();
  }
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
      throw err;
    });
};

export const hello = (data = {}) => {
  return post("/api/v1/auth/hello", data)
    .then((res) => {
      const old_token = $user.value?.token;
      const old_data = Object.assign({}, $user.value);

      if (res?.data && res?.token) {
        loginWithData(res.token, res.data);
        // restartWs();
      } else if (res?.data) {
        loginWithData(old_token, res.data);
      } else if (res?.token) {
        loginWithData(res.token, old_data);
        // restartWs();
      }

      return res;
    })
}

export const refresh_data = () => hello({ data: true });
export const refresh_token = () => hello({ token: true });

export const auth_init = async (fallback = '/') => {
  if ($user.value?.token) {

    const refresh_token_after = 30; // mins
    if ($user.value?.last_token_update && ($user.value?.last_token_update + (refresh_token_after * 6000)) < Date.now()) {
      const hello_token = await refresh_token();
      if (hello_token?.error) {
        $user.value = null;
        return redirect(fallback);
      }
      // restartWs();
      return;
    }

    const hello_data = await refresh_data();
    if (hello_data?.error) {
      $user.value = null;
      return redirect(fallback);
    }

    // restartWs();
  } else {
    // closeWs();
  }
}

export const login = (data) => {
  return post("/api/v1/auth/login", data)
    .then((res) => {
      if (res?.error) {
        throw new Error(res.error);
      }

      if (res?.token && res?.data) {
        loginWithData(res.token, res.data);
        // restartWs();
        return res;
      }

      throw new Error("Invalid server response");
    })
    .catch((err) => {
      createError("Login Error", err?.message);
      throw err;
    });
};

export const add_mail = (email) => {
  return post("/api/v1/auth/mail/add", { email })
    .then((res) => {
      if (res?.error) {
        throw new Error(res.error);
      }

      if (!$data.value) {
        $data.value = {};
      }

      $data.value.add_mail = Object.assign({}, {
        verification_id: res.verification_id,
      })

      return res;
    })
};

export const verify_mail = (verification_id, code) => {
  return post("/api/v1/auth/mail/verify", { verification_id, code })
    .then(async (res) => {
      if (res?.success && $data.value?.add_mail) {
        delete $data.value.add_mail;
      }

      await refresh_token();
      return res;
    })
};

export const logout = () => {
  return post("/api/v1/auth/logout")
    .then((res) => {
      $user.value = null;
      $redir.value = null;

      if (res?.error) {
        throw new Error(res.error);
      }

      // closeWs();
      return res;
    })
    .catch((err) => {
      $user.value = null;
      throw err;
    })
};

export const report = (type, data) => {
  data = JSON.parse(JSON.stringify(data));
  return post("/api/v1/report", { type, data })
    .then((res) => {
      if (res?.error) {
        throw new Error(res.error);
      }

      return res;
    })
    .catch((err) => {
      if (!$reports.value) {
        $reports.value = [];
      }

      $reports.value.push({ type, data });
      console.error(err);
    });
};


export const smtp_tester = (data) => {
  return post("/api/v1/mail/tools/smtp", data)
    .then((res) => {
      if (res?.error) {
        throw new Error(res.error);
      }

      return res;
    })
}

export const newsletter_add = (data) => {
  return post("/api/v1/newsletter/add", data)
    .then((res) => {
      if (res?.error) {
        throw new Error(res.error);
      }

      return res;
    })
}

export default post;
