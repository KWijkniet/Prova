<div style="margin: 0 auto; padding: 0; border: 0; background: #d8eaea;">
    <main style="background: #d8eaea;">
        <div style="position: relative; top: 25%; width: 100%;">
            <div style="background-color: #93d5ef; width: 80%; text-align: center; margin-left: 10%;">
                <h3 style="margin: 0 auto !important;">Prova</h3>
            </div>
        	<div style="background-color: #e9ecef; width: 80%; text-align: center; margin-left: 10%; padding: 30px 0px 30px 0px;">
              	<h3 style="margin: 0 auto !important;">Welcome!</h3>
                </br>
                <p>Press the button down below to register.</p>
                <div style="background-color: #93d5ef; width: 50%; margin-left: 25%; height: 40px;">
                    <a href="{{$link}}/register?invitation_token={{$invitation_token}}"><div style="font-size: 16px; text-align: center; padding-top: 8px;">Register</div></a>
                </div>
            </div>
            <div style="background-color: #515557; width: 80%; text-align: center; margin-left: 10%; font-size: 13px; color: white;">
                <p style="margin: 0 !important">Da Vinci's Exam Taker, 2019</p>
            </div>
        </div>
    </main>
</div>

<style>

  	* {
		margin: 0 auto;
  		padding: 0;
  		border: 0;
  	}

  	body {
        background: #d8eaea;
  	}

    @font-face {
        font-family: Azonix;
        src: url('../fonts/Azonix.otf');
    }

    #mail-wrapper {
  		position: relative;
  		top: 25%;
  		width: 100vw;
    }

    #mail-upper-container {
  		background-color: #93d5ef;
  		width: 80%;
  		text-align: center;
  		margin-left: 10%;
        font-family: Azonix;
  	}

    h3 {
        margin: 0 auto !important;
    }

  	#mail-middle-container {
  		background-color: #e9ecef;
  		width: 80%;
  		text-align: center;
  		margin-left: 10%;
        padding: 30 0 30 0px;
  	}

    #button-container {
        background-color: #93d5ef;
  		width: 40vw;
        margin-left: 25%;
    	height: 40px;
    }

    #register-button {
        font-size: 16px;
        text-align: center;
  		padding-top: 8px
    }

    #mail-below-container {
  		background-color: #515557;
  		width: 80%;
  		text-align: center;
  		margin-left: 10%;
        font-size: 13px;
        color: white;
  	}
</style>
