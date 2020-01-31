export function createLog(user, action) {
    new Promise((res, rej) => {
        axios.post('/api/log/createLog', {"id":user.id, "name":user.username, 'role_id': user.role_id, "action":action})
        .then((response) => {})
        .catch((err) =>{
            console.log(err);
        })
    });
}
