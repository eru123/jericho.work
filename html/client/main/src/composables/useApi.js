import { ref } from 'vue'
import useServerData from './useServerData'

export const $server = useServerData()
export const data = ref({})

export const post = (url, data) => {
    const base_url = $server?.BASE_URL || window.location.origin
    const url_obj = new URL(url, base_url)
    return fetch(url_obj, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    }).then(res => res.json())
}

export const get = (url, data) => {
    const base_url = $server?.BASE_URL || window.location.origin
    const url_obj = new URL(url, base_url)
    for (const key in data) {
        url_obj.searchParams.append(key, data[key])
    }
    return fetch(url_obj).then(res => res.json())
}

export const loginWithData = (token, data) => {
    data.value = data
    data.value.token = token
}

export const logout = () => {
    data.value = {}
}

export const register = (data) => {
    return post('/api/v1/auth/register', data)
        .then(res => {
            if (res.success) {
                loginWithData(res?.token, res?.data)
            }
            return res
        })
}

export default {
    data,
    post,
    get
}