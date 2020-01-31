export function initialize(store, router) {
    axios.interceptors.response.use(null, (error) => {
        if (error.response.status == 401) {
            router.push('/');
        }

        return Promise.reject(error);
    });

    if (store.getters.currentUser) {
        setAuthorization(store.getters.currentUser.token);
    }
}

export function setAuthorization(token) {
    axios.defaults.headers.common["Authorization"] = `Bearer ${token}`
}

export function clearAuthorization() {
    axios.defaults.headers.common["Authorization"] = "undefined";
}
