<template>
    <div>
        <div class="jumbotron jumbotron-fluid quotePanel">
            <div class="col-md-10 offset-md-1">
                <h1 class="display-4">Welcome, {{ currentUser.username }}! [{{currentUser.school.name}}]</h1>
                <p class="lead">"{{rQuote}}"</p>
                <p class="lead">-{{rAuthor}}</p>
            </div>
        </div>
        <p v-if="error">{{error}}</p>
        <div class="col-md-10 offset-md-1 d-flex justify-content-between row mt-5 home-container" id="homeContainer" v-if="openExamData == null">

            <div class="home-item">
                <h4 class="text-center">SELECT AN EXAM:</h4>
                <div class="home-item-container">
                    <div class="input-group sticky-top">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" v-model="searchExam" placeholder="Search Exam">
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50px;">Year</th>
                                <th>Name</th>
                                <th>Education</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(exam, index) in getExamList" @click="selectExam($event.target.parentElement, index)">
                                <td>{{exam.year}}</td>
                                <td>{{exam.name}}</td>
                                <td>{{exam.education.name}}</td>
                                <td>
                                    <router-link :to="{ name: 'exampreview', params: { 'id': exam.id }}" class="fas fa-eye"></router-link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="home-devider">
                <i class="fas fa-angle-double-right"></i>
            </div>

            <div class="home-item">
                <h4 class="text-center">SELECT A STUDENT:</h4>
                <div class="home-item-container">
                    <div class="input-group sticky-top">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" v-model="searchStudent" placeholder="Search Student">
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50px;">Year</th>
                                <th style="width: 100px;">OV</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, index) in getStudentList" @click="selectStudent($event.target.parentElement, index)">
                                <td>{{student.year}}</td>
                                <td>{{student.ov}}</td>
                                <td>{{student.name}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="home-devider">
                <i class="fas fa-angle-double-right"></i>
            </div>

            <div class="home-item">
                <h4 class="text-center">OVERVIEW:</h4>
                <div class="home-item-container" style="padding: 5px;">
                    <h5 class="text-center">Student Info:</h5>
                    <table class="table borderless">
                        <tr>
                            <th style="width: 50px;">Name:</th>
                            <td v-if="selectedStudent != null">{{selectedStudent.name}}</td>
                        </tr>
                        <tr>
                            <th style="width: 50px;">OV:</th>
                            <td v-if="selectedStudent != null">{{selectedStudent.ov}}</td>
                        </tr>
                        <tr>
                            <th style="width: 50px;">Year:</th>
                            <td v-if="selectedStudent != null">{{selectedStudent.year}}</td>
                        </tr>
                    </table>
                    <br />
                    <h5 class="text-center">Exam Info:</h5>
                    <table class="table borderless">
                        <tr>
                            <th style="width: 50px;">Name:</th>
                            <td v-if="selectedExam != null">{{selectedExam.name}}</td>
                        </tr>
                        <tr>
                            <th style="width: 50px;">Year:</th>
                            <td v-if="selectedExam != null">{{selectedExam.year}}</td>
                        </tr>
                    </table>
                    <br />
                    <h5 class="text-center">Examinator 01:</h5>
                    <table class="table borderless">
                        <tr>
                            <th style="width: 50px;">Name:</th>
                            <td>{{currentUser.username}}</td>
                        </tr>
                    </table>
                    <br />
                    <h5 class="text-center">Examinator 02:</h5>
                    <table class="table borderless">
                        <tr>
                            <th style="width: 50px;">Name:</th>
                            <td v-if="instance != null">{{instance.name}}</td>
                        </tr>
                    </table>
                    <button class="btn btn-primary" id="startExamButton" type="button" name="startExamButton" v-on:click="startExam()" disabled>Start Exam</button>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col align-self-center" id="inprogressOverlay" v-if="openExamData != null">
                    <div class="overlayLeft" style="display:table;overflow:hidden;">
                        <div style="display:table-cell;vertical-align:middle;">
                            <h2 class="fas fa-exclamation-triangle"></h2>
                        </div>
                    </div>
                    <div class="overlayRight" style="display:table;overflow:hidden;">
                        <div style="display:table-cell;vertical-align:middle;">
                            <h4>There is already an exam in progress. Please finish the exam before you can start a new one.</h4>
                            <button class="warningButton" type="button" name="button"><router-link :to="{ name: 'examsheet', params: { id: openExamData.id }}">Continue</router-link></button>
                            <button class="warningButton" type="button" name="button" v-on:click="delInstance(openExamData.id)">Cancel</button>
                            <p style="font-size:10px;">(NOTE: IF YOU PRESS THE CANCEL BUTTON, THE CURRENT EXAM PROGRESS WILL BE REMOVED!)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'home',
        data() {
            return {
                searchExam: '',
                searchStudent: '',

                exams: [],
                selectedExam: null,
                prevSelectedExamElem: null,

                students: [],
                selectedStudent: null,
                prevSelectedStudentElem: null,
                instance: null, //get instance when an examinator already started an exam on this student

                educations: [],

                rQuote: "",
                rAuthor: "",

                error: null,

                openExamData: null
            };
        },
        computed: {
            getExamList() {
                if(this.exams.length == 0) {return [];}

                const value= this.searchExam.toLowerCase();
                return this.exams.filter(function(exam){
                    return exam.name.toLowerCase().indexOf(value) > -1 ||
                            exam.year.toLowerCase().indexOf(value) > -1
                });
            },
            getStudentList() {
                if(this.exams.length == 0) {return [];}

                const value= this.searchStudent.toLowerCase();
                return this.students.filter(function(student){
                    return student.name.toLowerCase().indexOf(value) > -1 ||
                            student.ov.toLowerCase().indexOf(value) > -1 ||
                            student.year.toLowerCase().indexOf(value) > -1
                });
            },
            currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                },
                set: function () {
                }
            }
        },
        methods: {
            getExams() {
                new Promise((res, rej) => {
                    axios.post('/api/home/getExams', {id:this.currentUser.school_id})
                    .then((response) => {
                        this.exams = response.data;
                    })
                    .catch((err) =>{
                        this.error = err.response.data.message;
                    })
                });
            },

            selectExam(elem, id) {
                if(this.prevSelectedExamElem != null){
                    this.prevSelectedExamElem.classList.remove("selected");
                }
                this.selectedExam = this.exams[id];
                elem.classList.add("selected");
                this.prevSelectedExamElem = elem;

                if(this.selectedExam != null && this.selectedStudent != null){
                    document.getElementById("startExamButton").disabled = false;
                }
            },

            getStudents() {
                new Promise((res, rej) => {
                    axios.get('/api/home/getStudents')
                    .then((response) => {
                        this.students = response.data;
                    })
                    .catch((err) =>{
                        this.error = err.response.data.message;
                    })
                });
            },

            selectStudent(elem, id) {
                if(this.prevSelectedStudentElem != null){
                    this.prevSelectedStudentElem.classList.remove("selected");
                }
                this.selectedStudent = this.students[id];
                elem.classList.add("selected");
                this.prevSelectedStudentElem = elem;
                if(this.selectedExam != null && this.selectedStudent != null){
                    document.getElementById("startExamButton").disabled = false;
                }
            },


            startExam(){
                var self = this;

                if (confirm('Are you sure you want to start this exam?')){
                    var data = {
                        'student_id':this.selectedStudent.id,
                        'exam_id':this.selectedExam.id,
                        'examiner_id':this.currentUser.id
                    }

                    new Promise((res, rej) => {
                        axios.post('/api/home/makeexam', {data:data})
                        .then((response) => {
                            if(response.data.status == "error"){
                                alert(response.data.message);
                            } else if (response.data.status == "succeed") {
                                self.checkToStart(response.data.message , data);
                            }
                        })
                        .catch((err) =>{
                        })
                    })
                }
            },
            getStartedInstance() {
                new Promise((res, rej) => {
                    axios.post('/api/home/getStartedInstance', {id:this.currentUser.id})
                    .then((response) => {
                        if(response.data != null) {
                            this.openExamData = response.data;
                        }
                    })
                    .catch((err) =>{
                    })
                })
            },
            checkToStart(id , data){
                let self = this;
                let attempts = 0;
                let interval = setInterval(()=>{
                    attempts ++;
                    new Promise((res, rej) => {
                        axios.post('/api/home/checkexam', {data:data, id:id})
                            .then((response) => {
                                if(response.data >= 1){
                                    clearInterval(interval);
                                    self.$router.push({name:'examsheet', params: {'id': id} });
                                    clearInterval(interval);
                                }
                                if(attempts >= 10){
                                    clearInterval(interval);
                                    self.delInstance(id);
                                    alert('time out instances where not found');
                                    clearInterval(interval);
                                }
                            })
                            .catch((err) =>{
                                alert('error found');
                            })
                    })
                } , 2000)

            },
            delInstance(id){
                new Promise((res, rej) => {
                    axios.post('/api/home/delInstance', {id:id})
                    .then((response) => {
                        this.openExamData = null;
                    })
                })
            },

            getRandomQuote(){
                var quoteArray = ["Success is most often achieved by those who don't know that failure is inevitable.","Things work out best for those who make the best of how things work out.","Courage is grace under pressure.","If you are not willing to risk the usual, you will have to settle for the ordinary.","Learn from yesterday, live for today, hope for tomorrow. The important thing is not to stop questioning.","Take up one idea. Make that one idea your life -- think of it, dream of it, live on that idea. Let the brain, muscles, nerves, every part of your body be full of that idea, and just leave every other idea alone. This is the way to success.","Sometimes you can't see yourself clearly until you see yourself through the eyes of others.","All our dreams can come true if we have the courage to pursue them.","It does not matter how slowly you go, so long as you do not stop.","Success is walking from failure to failure with no loss of enthusiasm.","Someone is sitting in the shade today because someone planted a tree a long time ago.","Whenever you see a successful person, you only see the public glories, never the private sacrifices to reach them.","Don't cry because it's over, smile because it happened.","Success? I don't know what that word means. I'm happy. But success, that goes back to what in somebody's eyes success means. For me, success is inner peace. That's a good day for me.","You only live once, but if you do it right, once is enough.","Opportunities don't happen. You create them.","Once you choose hope, anything's possible.","Try not to become a person of success, but rather try to become a person of value.","There is no easy walk to freedom anywhere, and many of us will have to pass through the valley of the shadow of death again and again before we reach the mountaintop of our desires.","It is not the strongest of the species that survive, nor the most intelligent, but the one most responsive to change.","The best and most beautiful things in the world cannot be seen or even touched -- they must be felt with the heart.","Great minds discuss ideas; average minds discuss events; small minds discuss people.","Live as if you were to die tomorrow. Learn as if you were to live forever.","The best revenge is massive success.","The difference between winning and losing is most often not quitting.","I have not failed. I've just found 10,000 ways that won't work.","When you cease to dream you cease to live.","A successful man is one who can lay a firm foundation with the bricks others have thrown at him.","May you live every day of your life.","No one can make you feel inferior without your consent.","Failure is another steppingstone to greatness.","The whole secret of a successful life is to find out what is one's destiny to do, and then do it.","If you're not stubborn, you'll give up on experiments too soon. And if you're not flexible, you'll pound your head against the wall and you won't see a different solution to a problem you're trying to solve.","If you're going through hell, keep going.","In order to be irreplaceable one must always be different.","What seems to us as bitter trials are often blessings in disguise.","You miss 100 percent of the shots you don't take.","The distance between insanity and genius is measured only by success.","The way I see it, if you want the rainbow, you gotta put up with the rain.","To me, business isn't about wearing suits or pleasing stockholders. It's about being true to yourself, your ideas and focusing on the essentials.","The longer I live, the more beautiful life becomes.","Happiness is a butterfly, which when pursued, is always beyond your grasp, but which, if you will sit down quietly, may alight upon you.","You must expect great things of yourself before you can do them.","If you can't explain it simply, you don't understand it well enough.","You can't please everyone, and you can't make everyone like you.","There are two types of people who will tell you that you cannot make a difference in this world: those who are afraid to try and those who are afraid you will succeed.","I believe every human has a finite number of heartbeats. I don't intend to waste any of mine.","Start where you are. Use what you have. Do what you can.","Don't limit yourself. Many people limit themselves to what they think they can do. You can go as far as your mind lets you. What you believe, remember, you can achieve.","People ask, 'What's the best role you've ever played?' The next one.","The two most important days in your life are the day you are born and the day you find out why.","I find that the harder I work, the more luck I seem to have.","It often requires more courage to dare to do right than to fear to do wrong.","Success is the sum of small efforts, repeated day-in and day-out.","As you grow older, you will discover that you have two hands, one for helping yourself, the other for helping others.","If you want to achieve excellence, you can get there today. As of this second, quit doing less-than-excellent work.","If your actions inspire others to dream more, learn more, do more, and become more, you are a leader.","All progress takes place outside the comfort zone.","The more you praise and celebrate your life, the more there is in life to celebrate.","You may only succeed if you desire succeeding; you may only fail if you do not mind failing.","A dream doesn't become reality through magic; it takes sweat, determination, and hard work.","Only put off until tomorrow what you are willing to die having left undone.","The biggest risk is not taking any risk... In a world that's changing really quickly, the only strategy that is guaranteed to fail is not taking risks.","We become what we think about most of the time, and that's the strangest secret.","Do one thing every day that scares you.","The only place where success comes before work is in the dictionary.","Don't be afraid to give up the good to go for the great.","Your work is going to fill a large part of your life, and the only way to be truly satisfied is to do what you believe is great work. And the only way to do great work is to love what you do. If you haven't found it yet, keep looking. Don't settle. As with all matters of the heart, you'll know when you find it.","Don't worry about failure; you only have to be right once.","Though no one can go back and make a brand-new start, anyone can start from now and make a brand-new ending.","Nothing great was ever achieved without enthusiasm.","Twenty years from now you will be more disappointed by the things that you didn't do than by the ones you did do. So throw off the bowlines. Sail away from the safe harbor. Catch the trade winds in your sails. Explore. Dream. Discover.","Keep your face to the sunshine and you can never see the shadow.","The first step toward success is taken when you refuse to be a captive of the environment in which you first find yourself.","One of the greatest diseases is to be nobody to anybody.","Identity is a prison you can never escape, but the way to redeem your past is not to run from it, but to try to understand it, and use it as a foundation to grow.","The successful warrior is the average man, with laser-like focus.","Rarely have I seen a situation where doing less than the other guy is a good strategy.","If you always do what interests you, at least one person is pleased.","Keep on going, and the chances are that you will stumble on something, perhaps when you are least expecting it. I never heard of anyone ever stumbling on something sitting down.","I avoid looking forward or backward, and try to keep looking upward.","You can't connect the dots looking forward; you can only connect them looking backward. So you have to trust that the dots will somehow connect in your future. You have to trust in something -- your gut, destiny, life, karma, whatever. This approach has never let me down, and it has made all the difference in my life.","Life is short, and it is here to be lived.","Everything you can imagine is real.","Change will not come if we wait for some other person or some other time. We are the ones we've been waiting for. We are the change that we seek.","If you want to make a permanent change, stop focusing on the size of your problems and start focusing on the size of you!","Successful people do what unsuccessful people are not willing to do. Don't wish it were easier; wish you were better.","It is never too late to be what you might have been.","If you love what you do and are willing to do what it takes, it's within your reach. And it'll be worth every minute you spend alone at night, thinking and thinking about what it is you want to design or build.","In my experience, there is only one motivation, and that is desire. No reasons or principle contain it or stand against it.","In the midst of movement and chaos, keep stillness inside of you.","Success does not consist in never making mistakes but in never making the same one a second time.","I don't want to get to the end of my life and find that I lived just the length of it. I want to have lived the width of it as well.","As we look ahead into the next century, leaders will be those who empower others.","Our greatest fear should not be of failure... but of succeeding at things in life that don't really matter.","Be yourself. Everyone else is already taken.","If you don't design your own life plan, chances are you'll fall into someone else's plan. And guess what they have planned for you? Not much.","But you have to do what you dream of doing even while you're afraid.","To be successful, you must accept all challenges that come your way. You can't just accept the ones you like.","Be patient with yourself. Self-growth is tender; it's holy ground. There's no greater investment.","Many of life's failures are people who did not realize how close they were to success when they gave up.","If you can copy and paste one hundred and one inspirational quotes, you can do anything"];
                var authorArray = ["Coco Chanel","John Wooden","Ernest Hemingway","Jim Rohn","Albert Einstein","Swami Vivekananda","Ellen DeGeneres","Walt Disney","Confucius","Winston Churchill","Warren Buffett","Vaibhav Shah","Dr. Seuss","Denzel Washington","Mae West","Chris Grosser","Christopher Reeve","Albert Einstein","Nelson Mandela","Charles Darwin","Helen Keller","Eleanor Roosevelt","Mahatma Gandhi","Frank Sinatra","Walt Disney","Thomas Edison","Malcolm Forbes","David Brinkley","Jonathan Swift","Eleanor Roosevelt","Oprah Winfrey","Henry Ford","Jeff Bezos","Winston Churchill","Coco Chanel","Oscar Wilde","Wayne Gretzky","Bruce Feirstein","Dolly Parton","Richard Branson","Frank Lloyd Wright","Nathaniel Hawthorne","Michael Jordan","Albert Einstein","Katie Couric","Ray Goforth","Neil Armstrong","Arthur Ashe","Mary Kay Ash","Kevin Kline","Mark Twain","Thomas Jefferson","Abraham Lincoln","Robert Collier","Audrey Hepburn","Thomas J. Watson","John Quincy Adams","Michael John Bobak","Oprah Winfrey","Philippos","Colin Powell","Pablo Picasso","Mark Zuckerberg","Earl Nightingale","Eleanor Roosevelt","Vidal Sassoon","John D. Rockefeller","Steve Jobs","Drew Houston","Carl Bard","Ralph Waldo Emerson","Mark Twain","Helen Keller","Mark Caine","Mother Teresa","Jay-Z","Bruce Lee","Jimmy Spithill","Katharine Hepburn","Charles F. Kettering","Charlotte Bronte","Steve Jobs","Kate Winslet","Picasso","Barack Obama","T. Harv Eker","Jim Rohn","George Eliot","Steve Wozniak","Jane Smiley","Deepak Chopra","George Bernard Shaw","Diane Ackerman","Bill Gates","Francis Chan","Oscar Wilde","Jim Rohn","Arianna Huffington","Mike Gafka","Stephen Covey","Thomas A. Edison","Daniel Cole"];
                var randomInt = Math.floor(Math.random() * quoteArray.length);
                this.rQuote = quoteArray[randomInt];
                this.rAuthor = authorArray[randomInt];
            }
        },

        mounted(){
            this.getExams();
            this.getStudents();
            this.getRandomQuote();
            this.getStartedInstance();
        }
    }
</script>

<style>
.quotePanel {
    background-color: #90c7b5;
}

.home-container{
    max-height: 750px;
    min-height: 750px;
}

.home-item{
    width: 30%;
    min-height: 100%;
    max-height: calc(750px - 34px);
}

.home-devider{
    width: 5%;
    min-height: 100%;
}

.home-devider i{
    width: 100%;
    font-size: 2em;
    line-height: 750px;
    text-align: center;
}

.home-item-container{
    min-height: calc(100% - 34px);
    max-height: calc(100% - 34px);
    overflow-y: auto;
    border: 1px solid grey;
    position: relative;
    background-color: #eaeaea;
    border-radius: 3px;
}

.input-group-text{
    background-color: white;
    border: none;
}

.input-group{
    width: calc(100% - 2px);
    margin-left: 1px;
}

.home-item-container input{
    border: none;
    outline: none;
    padding-left: 0;
    background-color: transparent;
}

.home-item-container span {
    background-color: transparent;
}

.home-item-container input:focus, .home-item-container input:focus{
    outline: none;
    box-shadow: none;
    background-color: transparent;
}

.home-item-container table{
    width: 100%;
}

.borderless th, .borderless td{
    border: none;
}

.home-item-container table:not(.borderless) tr{
    border-bottom: 1px solid grey;
}

.home-item-container table tbody tr:hover{
    /* background-color: rgb(240,240,240); */
    background-color: #b9b9b9;
    cursor: pointer;
}

.home-item-container table .selected{
    background-color: rgb(240,240,240);
}

.home-item-container table tr th, .home-item-container table tr td{
    padding: 10px;
}

#startExamButton{
    position: absolute;
    right: 5px;
    bottom: 5px;
}

/* Loading Animation on login */
.loader{
  margin: 0 0 2em;
  height: 100px;
  width: 20%;
  text-align: center;
  margin: 0 auto 1em;
  vertical-align: top;
}

svg path,
svg rect{
  fill: #55ae90;
}

#inprogressOverlay {
    display: flex;
    width: calc(100% - 40px);
    height: 100%;
    margin: 20px;
    padding: 20px;
    text-align: center;
    color: white;
    font-weight: bold;
}

.overlayLeft {
    background-color: #7bbfa7;
    margin: 0;
    width: 100px;
    height: 22vh;
    border-radius: 10px 0px 0px 10px;
}

.overlayLeft h2 {
    color: white !important;
}

.overlayRight {
    background-color: #90c7b5;
    padding: 10px;
    border-radius: 0px 10px 10px 0px;
    height: 22vh;
}

.warningButton {
    padding: 5px 10px;
    border: 2px solid #7bbfa7;
    border-radius: 3px;
    background-color: #a9d6c6;
    color: white;
    font-weight: bold;
    height: 4vh;
}

.warningButton a, .warningButton a:hover {
    text-decoration: none;
    color: white;
}

/* Loading Animation on login */

@media (max-width: 935px) {
    .home-item {
        width: 100%;
        margin-bottom: 2vh;
    }

    .home-container i {
        display: none;
    }
}

@media (max-width: 767px) {
    .home-item-container {
        margin-left: 4vw;
    }
}
</style>
