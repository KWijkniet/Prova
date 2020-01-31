import { getLocalUser } from "./helpers/auth";
import { updateSession } from "./helpers/auth";
import { deleteSession } from "./helpers/auth";
import { getLocalSession } from "./helpers/auth";
import { clearAuthorization } from "./helpers/general";
import { createLog } from "./helpers/log";

var user = getLocalUser();
var session = null;
if(user){
    session = getLocalSession();
}

export default {
    state: {
        currentUser: user,
        session: session
    },
    mutations: {
        updateSession() {
            console.log("updatedSession");
            localStorage.removeItem("user");
            updateSession(this.state.currentUser.id);
            this.state.session = getLocalSession();
        },
        loginSuccess(){
            console.log("logged in");
            this.state.currentUser = JSON.parse(localStorage.getItem("user"));
            this.state.session = JSON.parse(localStorage.getItem("session"));
            if(localStorage.getItem("data")){
                this.state.data = localStorage.getItem("data");
            }else{
                localStorage.setItem('data', '');
                this.state.data = '';
            }
            createLog(this.state.currentUser, "has logged in");
        },
        logout(){
            console.log("logged out");
            deleteSession(this.state.currentUser.id);
            clearAuthorization();
            createLog(this.state.currentUser, "has logged out");
            this.state.currentUser = null;
            this.state.session = null;
            this.state.data = null;
            localStorage.removeItem("user");
            localStorage.removeItem("session");
            localStorage.removeItem("data");
        }
    }
};
