export function acl(role) {
    const user = JSON.parse(localStorage.getItem("user") ?? "null")

    if (!user || !user?.roles || !Array.isArray(roles)) {
        return false;
    }

    const roles = new Set(user.roles.map((e) => String(e).toLocaleLowerCase()).filter(e => !!e));
    const accepted = new Set(role.split("|").map((e) => String(e).toLocaleLowerCase()).filter(e => !!e));
    return roles.intersection(accepted).size > 0;
}

export function aclToArray(role, data) {
    if (acl(role)) {
        return [data]
    }
    return [];
}

export default {}