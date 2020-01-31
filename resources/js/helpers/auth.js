import { setAuthorization } from "./general";

export function getLocalUser() {
    const userStr = localStorage.getItem("user");

    if(!userStr) {
        return null;
    }
    return JSON.parse(userStr);
}

export function getLocalSession() {
    const userStr = localStorage.getItem("session");

    if(!userStr) {
        return null;
    }
    return JSON.parse(userStr);
}

export function updateSession(userId){
    return new Promise((res, rej) => {
        axios.post('/api/auth/updateSession', {'userId': userId})
        .then((response) => {
            localStorage.setItem("session", JSON.stringify(response.data));
        })
        .catch((err) =>{})
    });
}

export function deleteSession(userId){
    return new Promise((res, rej) => {
        axios.post('/api/auth/logout', {'userId': userId})
        .then((response) => {})
        .catch((err) =>{})
    });
}
