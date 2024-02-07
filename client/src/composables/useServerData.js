import { reactive } from "vue"

const data = reactive(window?.__SERVER_DATA__ || {})

export const useServerData = () => {
    return data
}

export const REQUEST_URI = data?.REQUEST_URI || "/"
export const BASE_URL = data?.BASE_URL || window.location.origin

export default useServerData
