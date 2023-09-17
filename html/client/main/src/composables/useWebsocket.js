import useServerData from "./useServerData";
import usePersistentData from "./usePersistentData";
import { report } from "./useApi";

const $server = useServerData();
const $user = usePersistentData("user", null);

var ws;

export const send = (data) => {
    ws.send(JSON.stringify(data));
};

export const useWebsocket = () => {
    const token = $user?.value?.token || "secretJWTToken";
    ws = new WebSocket($server.value?.WS_HOST);

    ws.onopen = () => send({ action: "auth", token });
    ws.onmessage = (e) => {
        const data = JSON.parse(e.data);
        if (data?.type === "error") {
            console.log("WS Error: ", data);
        }
    };

    ws.onclose = () => ws = null;
    ws.onerror = (e) => {
        report("error", e);
        if (ws) ws.close();
        ws = null;
    }

    return ws;
};