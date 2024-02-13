export function acl(role, user = null) {
    if (user === null) {
        user = JSON.parse(localStorage.getItem("user") ?? "null")
    }

    if (!user || !user?.roles || !Array.isArray(roles)) {
        return false;
    }

    const roles = new Set(user.roles.map((e) => String(e).toLocaleLowerCase()).filter(e => !!e));
    const accepted = new Set(role.split("|").map((e) => String(e).toLocaleLowerCase()).filter(e => !!e));
    return roles.intersection(accepted).size > 0;
}

export function aclToArray(role, data, user = null) {
    if (acl(role, user)) {
        return [data]
    }
    return [];
}

export default {}