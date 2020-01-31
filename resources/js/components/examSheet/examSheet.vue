<template>
    <div>
        <h1 style="text-align:center;" class="groups tableBackground">{{ examName }}</h1>
        <div v-for="(group, groupIndex) in groups" class="groups allowNewLine tableBackground">
            <p class="question-title">{{group.name}}:</p>
            <p class="pd">Perceived, Doubt</p>
            <div v-for="(question, questionIndex) in group.questions">
                <div class="question row">
                    <p class="col question-required-mark">({{cesuurs.marks[question.requiredMark]}})</p>
                    <p class="col" v-on:click="question.show = !question.show">
                        <i v-on:click="toggleQuestion(groupIndex + '-' + questionIndex + '_question')" class="fas fa-info-circle"></i>
                        {{question.question}}
                    </p>
                    <div class="col question-answer">
                        <input v-on:click="saveAnswers()" :checked="savedAnswers && savedAnswers.groups[groupIndex].answers[questionIndex].percieved == 1" :id="groupIndex + '-' + questionIndex + '_percieved'" type="checkbox">
                        <input v-on:click="saveAnswers()" :checked="savedAnswers && savedAnswers.groups[groupIndex].answers[questionIndex].doubt == 1" :id="groupIndex + '-' + questionIndex + '_doubt'" type="checkbox">
                        <i v-on:click="toggleComment(groupIndex + '-' + questionIndex + '_comment')" class="fas fa-comment"></i>
                    </div>
                </div>
                <p :id="groupIndex + '-' + questionIndex + '_question'" class="explanation hide col-sm-12">{{question.explanation}}</p>
                <textarea v-on:change="saveAnswers()" :value="(savedAnswers && savedAnswers.groups[groupIndex].answers[questionIndex].comment.length > 0) ? savedAnswers.groups[groupIndex].answers[questionIndex].comment : ''" :id="groupIndex + '-' + questionIndex + '_comment'" class="comment hide col-sm-12" v-on:keydown.enter.prevent="allowNewLine(groupIndex + '-' + questionIndex + '_comment')" oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"'></textarea>
            </div>
        </div>
        <button class="submitExam" v-on:click="submitAnswers()">Submit</button>
    </div>
</template>

<script>
export default {
    name: "examSheet",
    props: ['id'],
    data() {
        return {
            error: null,
            groups:  {},
            cesuurs: {},
            instance: {},
            prevQuestionId: null,
            prevCommentId: null,
            savedAnswers: null,
            examName: ""
       };
    },
    methods: {
        checkUser() {
            if (this.currentUser.role_id === 1) {
                this.$router.push({path: '/admin'});
            }
        },
        getInstance(){
            var self = this;
            new Promise((res, rej) => {
                axios.post('/api/examsheet/getinstance', {id: this.id, examiner_id: this.currentUser.id})
                .then((response) => {
                    this.instance = response.data;
                    if(this.instance == null){
                        alert('Access Denied');
                        self.$router.push({path :'/'});
                    }else{
                        if(this.instance['answers'] && this.instance['answers'].length > 0) {
                            this.savedAnswers = JSON.parse(this.instance['answers']);
                        }
                        this.getExam();
                    }
                })
              .catch((err) =>{
                    console.log(err);
                })
            });
        },

        getExam(){
            new Promise((res, rej) => {
                axios.get('/api/examsheet/getexam/' + this.instance.exam_id)
                .then((response) => {
                    this.groups = JSON.parse(response.data['template'])['groups'];
                    this.cesuurs = JSON.parse(response.data['template'])['cesuur'];
                    this.examName = response.data['name'];

                    for(var i = 0; i < this.groups.length; i++){
                        var group = this.groups[i];
                        for(var r = 0; r < group.questions.length; r++){
                            var question = group.questions[r];
                            question['show'] = false;
                        }
                    }
                })
                .catch((err) =>{
                    alert('Cannot get exam');
                })
            });
        },
        allowNewLine(id) {
            var elem = document.getElementById(id);
            elem.value = elem.value + "\n";
            return false;
        },

        toggleQuestion(id){
            document.getElementById(id).classList.toggle('hide');
            if(this.prevQuestionId != null && this.prevQuestionId != id){
                document.getElementById(this.prevQuestionId).classList.add('hide');
            }
            this.prevQuestionId = id;
        },
        toggleComment(id){
            document.getElementById(id).classList.toggle('hide');
            document.getElementById(id).focus();
            if(this.prevCommentId != null && this.prevCommentId != id){
                document.getElementById(this.prevCommentId).classList.add('hide');
            }
            this.prevCommentId = id;
        },
        convertAnswers(){
            var groups = [];
            for (var i = 0; i < this.groups.length; i++) {
                var group = this.groups[i];
                groups.push({
                    "name" : group.name,
                    "answers" : []
                });
                for (var r = 0; r < group.questions.length; r++) {
                    var data = {
                        percieved : document.getElementById(i + "-" + r + "_percieved").checked ? 1 : 0,
                        doubt : document.getElementById(i + "-" + r + "_doubt").checked ? 1 : 0,
                        comment : document.getElementById(i + "-" + r + "_comment").value
                    };

                    groups[i].answers[r] = data;
                }
            }
            var data = {
                "groups" : groups
            };
            return data;
         },
        saveAnswers(){
            var data = this.convertAnswers();
            new Promise((res, rej) => {
                axios.post('/api/examsheet/saveanswers',{'instance_id' : this.instance.id ,'answers': JSON.stringify(data)})

                .then((response) => {

                })
                .catch((err) =>{
                    console.log("error");
                    alert('Cannot save answers.44')
                })
            });
        },
        submitAnswers(){
            var self = this;
            var data = this.convertAnswers();
            new Promise((res, rej) => {
                axios.post('/api/examsheet/saveresults',{'instance_id' : this.instance.id ,'answers': JSON.stringify(data)})
                .then((response) => {
                    if(response.data.status == 'success'){
                        self.checkCombine();
                    }else{
                        self.$router.push({name: 'home'});
                    }
                })
                .catch((err) =>{
                    alert('Cannot save ress.')
                })
            });

        },
        checkCombine(){
            var self = this;
            var interval = setInterval(() => {
                new Promise((res, rej) => {
                    axios.post('/api/examsheet/checkcombine',{'instance_id' : this.instance.id})
                    .then((response) => {
                        if(response.data == 0){
                            clearInterval(interval);
                            self.$router.push({name: 'examcombine', params:{'instanceId': this.instance.id}});
                        }
                    })
                    .catch((err) =>{
                        alert('Cannot save result    s.')
                    })
                });
            }, 2000)
        },
    },
    mounted(){
        this.getInstance();
    },
    computed:{
        currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                },
                set: function () {
                }
            }
    },

}
</script>

<style scoped>
    .error {
        text-align: center;
        color: red;
    }

    i{
        cursor: pointer;
    }

    .groups{
        width: 80%;
        display: block;
        margin: 0 auto;
        border: 1px solid black;
        margin-bottom: 25px;
        clear: both;
    }

    .hide{
        display: none;
    }

    .question{
        margin-top: 25px;
    }

    .question-title{
        font-size: 1.25em;
        font-weight: 100;
    }

    .question-required-mark{
        max-width: 100px;
        text-align: center;
    }

    .question-answer{
        max-width: 200px;
        text-align: center;
    }

    .question-answer input, .question-answer i{
        margin-right: 25px;
    }

    .explanation, .comment{
        max-height: 250px;
        overflow-y: auto;
    }

    .header-info{
        width: 80%;
        display: block;
        margin: 0 auto;
        text-align: right;
        padding-right: 80px;
    }

    .allowNewLine {
        white-space: pre-line;
    }

    .pd {
        position: absolute;
        right: 15.5vw;
        margin-top: -1vh;
    }

    .submitExam {
        position: absolute;
        right: 10vw;
        border: 1px solid darkgray;
        background-color: #eaeaea;
        border-radius: 3px;
        padding: 5px 10px;
    }
</style>
