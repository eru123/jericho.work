import useServerData from "./useServerData";
import usePersistentData from "./usePersistentData";
import { report } from "./useApi";

const $server = useServerData();
const $user = usePersistentData("user", null);

var ws;
var callbacks = [];

export const send = (data) => {
    ws.send(JSON.stringify(data));
};

export const useWebsocket = () => {
    if (ws) return ws;

    const token = $user?.value?.token || "secretJWTToken";
    ws = new WebSocket($server?.WS_HOST);

    ws.onopen = () => {
        send({ action: "auth", token });
        callbacks.forEach(({ event, callback }) => {
            if (event === "open") callback();
        });
    }

    ws.onmessage = (e) => {
        try {
            const data = JSON.parse(e.data);

            callbacks.forEach(({ event, callback }) => {
                if (event === "message") callback(data);
            });

            if (data?.event) {
                callbacks.forEach(({ event, callback }) => {
                    if (event === data.event) callback(data);
                });
            }
            if (data?.type === "error") {
                callbacks.forEach(({ event, callback }) => {
                    if (event === "error") callback(data);
                });
            }
        } catch (e) {
            report("error", {
                message: e?.message || "Unknown error",
                origin: "useWebsocket.js - onmessage",
                data: e?.data,
                error: e
            })
            callbacks.forEach(({ event, callback }) => {
                if (event === "error") callback(e);
            });
        }
    };

    ws.onclose = () => {
        callbacks.forEach(({ event, callback }) => {
            if (event === "close") callback();
        });
        ws = null;
    }
    ws.onerror = (e) => {
        report("error", {
            message: e?.message || "Unknown error",
            origin: "useWebsocket.js - onerror",
            data: e?.data,
            error: e
        })
        callbacks.forEach(({ event, callback }) => {
            if (event === "error") callback(e);
        });
        if (ws) ws.close();
        ws = null;
    }

    return ws;
};

export default useWebsocket;
export const useWS = useWebsocket;
export const close = () => {
    if (ws) ws.close();
    ws = null;
}
export const restart = () => {
    if (ws) ws.close();
    ws = null;
    return useWebsocket();
}

export const subscribe = (channel) => send({ action: "subscribe", channel });
export const unsubscribe = (channel) => send({ action: "unsubscribe", channel });

export const on = (event, callback) => {
    callbacks.push({ event, callback });
}