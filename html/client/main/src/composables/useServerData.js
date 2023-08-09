import { reactive } from "vue"

const data = reactive(window?.__SERVER_DATA__ || {})

export const useServerData = () => {
    return data
}

export default useServerData
