<template>
    <div class="container">
        <div class="login row justify-content-center">
            <div class="login-body">
                <div class="rgstr-btn splits">
    				<h1 class="title">PROVA</h1>
                    <h5 class="slogan">Da Vinci's Exam Taker</h5>
			    </div>
                <div class="wrapper">
                    <h1 class="title login-logo">PROVA</h1>
                    <form @submit.prevent="Login" id="login" tabindex="500" autocomplete="off">
                        <h3>Login</h3>
                        <div class="mail">
                            <input type="email" v-model="form.email" class="form-control" placeholder="Email Address" autocomplete="off" required>
                            <label for="email">Email Address:</label>
                        </div>
                        <div class="passwd">
                            <input type="password" v-model="form.password" class="form-control" placeholder="Password" autocomplete="off" required>
                            <label for="password">Password:</label>
                        </div>
                        <div class="form-group row" v-if="error != null">
                            <p class="error">
                                {{ error }}
                            </p>
                        </div>
                        <div class="submit">
                            <button v-show="!showLoadAnim" id="loginButton" class="dark">Login</button>
                            <div v-show="showLoadAnim" id="loader" class="loader loader--style3">
                                <svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">
                                    <path fill="#000" d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z">
                                        <animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 25 25" to="360 25 25" dur="0.6s" repeatCount="indefinite" />
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {setAuthorization} from '../../helpers/general';

    export default {
        name: "login",
        data() {
            return {
                form: {
                    email: '',
                    password: ''
                },
                error: null,
                showLoadAnim: false
            };
        },
        methods: {
            Login() {
                this.showLoadAnim = true;
                var self = this;
                new Promise((res, rej) => {
                    axios.post('/api/auth/login', this.$data.form)
                    .then((response) => {
                        setAuthorization(response.data.access_token);

                        var data = JSON.parse(response.data.data);

                        localStorage.setItem("user", JSON.stringify(data.user));
                        localStorage.setItem("session", JSON.stringify(data.session));
                        self.$store.commit("loginSuccess");
                        self.$router.push('/examinerhome', ()=>{});
                    })
                    .catch((err) =>{
                        self.showLoadAnim = false;
                        self.$noty.error(err.response.data.message);
                    })
                });
            }
        }
    }
</script>
<style scoped>
.login {
    height: 100vh;
    width: 100vw;
    margin: 0;
    background: rgb(152,203,149);
    background: linear-gradient(90deg, rgba(152,203,149,1) 0%, rgba(82,172,139,1) 50%, rgba(146,211,237,1) 100%);
}

.container {
    margin: 0;
    padding: 0;
}

body {
    overflow: hidden !important;
}

.error {
    text-align: center;
    margin: 0 auto;
    color: red;
}

h1.title {
    text-align: center;
    padding: 8px;
    font-family: Azonix;
}

.body {
	background: #ff4931;
	transition: all .5s;
	padding: 1px;
}

.rgstr-btn {
    margin-left: 10%;
    padding: 0% 0% 0% 45%;
    line-height: 0px;
}

.rgstr-btn .title {
    font-size: 60px;
    background: linear-gradient(90deg, rgba(152,203,149,1) 0%, rgba(82,172,139,1) 50%, rgba(146,211,237,1) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.login-logo {
    background: linear-gradient(90deg, rgba(152,203,149,1) 0%, rgba(82,172,139,1) 50%, rgba(146,211,237,1) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    padding: 8% 0px 0px 0px !important;
    display: none;
}

.rgstr-btn .slogan {
    background: linear-gradient(90deg, rgba(152,203,149,1) 0%, rgba(82,172,139,1) 50%, rgba(146,211,237,1) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: bold;
}

.login-body {
    border-radius: 5px;
	width: 70%;
	margin: 100px auto;
	background: rgba(255, 255, 255, .5);
	/* min-height: 400px; */
    height: 50%;
	display: table;
	position: relative;
	box-shadow: 0 0 4px rgba(0, 0, 0, .14), 0 4px 8px rgba(0, 0, 0, .28);
}

.login-body>div {
	display: table-cell;
	vertical-align: middle;
	text-align: center;
	color: #fff;
}

.login-body button {
    background-color: #abd7c9;
	display: inline-block;
	padding: 5px 30px;
	border: 3px solid #fff;
	border-radius: 10px;
	background-clip: padding-box;
	position: relative;
	color: white;
	/* box-shadow: 0 0 4px rgba(0, 0, 0, .14), 0 4px 8px rgba(0, 0, 0, .28); */
	transition: all .25s;
}

.login-body button.dark {
    border: 2px solid #54ad8d;
}

.login-body .move button.dark {
	border-color: #e0b722;
	background: #e0b722;
}

.login-body .splits p {
	font-size: 18px;
}

.login-body button:active {
	box-shadow: none;
}

.login-body button:focus {
	outline: none;
}

.login-body>.wrapper {
    border-radius: 5px;
	position: absolute;
	width: 40%;
	height: 120%;
	top: -10%;
	left: 5%;
	background: #fff;
	box-shadow: 0 0 4px rgba(0, 0, 0, .14), 0 4px 8px rgba(0, 0, 0, .28);
	transition: all .5s;
	color: #303030;
	overflow: hidden;
}

.login-body .wrapper>form {
	padding: 15px 30px 30px;
	width: 100%;
	transition: all .5s;
	background: #fff;
	width: 100%;
}

.login-body .wrapper>form:focus {
	outline: none;
}

.login-body .wrapper #login {
	/* padding-top: 20%; */
	visibility: visible;
}

.login-body .wrapper #register {
	transform: translateY(-80%) translateX(100%);
	visibility: hidden;
}

.login-body .wrapper.move #register {
	transform: translateY(-80%) translateX(0%);
	visibility: visible;
}

.login-body .wrapper.move #login {
	transform: translateX(-100%);
	visibility: hidden;
}

.login-body .wrapper>form>div {
	position: relative;
	margin-bottom: 15px;
}

.login-body .wrapper label {
	position: absolute;
	top: -7px;
	font-size: 12px;
	white-space: nowrap;
	background: #fff;
	text-align: left;
	left: 15px;
	padding: 0 5px;
	color: #999;
	pointer-events: none;
}

.login-body .wrapper h3 {
	margin-bottom: 25px;
}

.login-body .wrapper input {
	height: 40px;
	padding: 5px 15px;
	width: 100%;
	border: solid 1px #999;
}

.login-body .wrapper input {
	height: 40px;
	padding: 5px 15px;
	width: 100%;
	border: solid 1px #999;
}

.login-body .wrapper input:focus {
	outline: none;
	border-color: #5aaf8c;
}

.login-body>.wrapper.move {
	left: 45%;
}

.login-body>.wrapper.move input:focus {
	border-color: #e0b722;
}

@media (max-width: 767px) {
	.login-body {
		padding: 5px;
		margin: 0;
		width: 100%;
		display: block;
        background-color: transparent;
        box-shadow: none;
	}
	.login-body>.wrapper {
		position: relative;
		height: auto;
		top: 0;
		left: 0;
		width: 100%;
	}
	.login-body>div {
		display: inline-block;
	}
	.splits {
		width: 50%;
		background: #fff;
		float: left;
	}
	.splits button {
		width: 100%;
		border-radius: 0;
		background: #505050;
		border: 0;
		opacity: .7;
	}
	.splits button.active {
		opacity: 1;
	}
	.splits button.active {
		opacity: 1;
		background: #ff4931;
	}
	.splits.rgstr-btn button.active {
		background: #e0b722;
	}
	.splits p {
		display: none;
	}
	.login-body>.wrapper.move {
		left: 0%;
	}
    .rgstr-btn {
        display: none !important;
    }
    .login-logo {
        display: block;
    }
}

input:-webkit-autofill,
textarea:-webkit-autofill,
select:-webkit-autofill {
	box-shadow: inset 0 0 0 50px #fff
}


/* Loading Animation on login */
.loader{
  margin: 0 0 2em;
  height: 100px;
  width: 20%;
  text-align: center;
  padding: 1em;
  margin: 0 auto 1em;
  vertical-align: top;
}

svg path,
svg rect{
  fill: #55ae90;
}
/* Loading Animation on login */
</style>
