export function getRoleByString(roleName) {
    var roles = {
        'examiner' : 4,
        'admin' : 3,
        'superAdmin': 2,
        'ceo': 1
    }
    return roles[roleName];
}
