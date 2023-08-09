import { reactive } from "vue"

export const useServerData = () => {
    return reactive(window?.__SERVER_DATA__ || {})
}

export default useServerData
