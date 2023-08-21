import useServerData, { BASE_URL } from "./useServerData";
import usePersistentData from "./usePersistentData";
import { createError } from "./useDialog";
export const $server = useServerData();
export const localData = usePersistentData("user", null);

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
    localData.value = data;
    localData.value.token = token;
};

export const logout = () => {
    localData.value = null;
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
            return null;
        })
};

export default {
    data: localData,
    post,
    get,
};
