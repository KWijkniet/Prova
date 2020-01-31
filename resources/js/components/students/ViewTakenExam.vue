<template>
    <div class="row page">
        <div class="col-md-9">
            <div class="leftSide">
                <p class="header-info">perceived, Doubt</p>
                <div v-for="(group, groupIndex) in groups" class="groups">
                    <p class="question-title">{{group.name}}:</p>
                    <div v-for="(question, questionIndex) in group.questions">
                        <div class="question row">
                            <p class="col question-required-mark allowNewLine">({{cesuurs.marks[question.requiredMark]}})</p>
                            <p class="col allowNewLine" v-on:click="question.show = !question.show">
                                <i v-on:click="toggleQuestion(groupIndex + '-' + questionIndex + '_question')" class="fas fa-info-circle"></i>
                                {{question.question}}
                            </p>
                            <div class="col question-answer">
                                <input type="checkbox" :checked="checkAnswer(groupIndex, questionIndex)" disabled>
                                <input type="checkbox" :checked="checkDoubt(groupIndex, questionIndex)" disabled>

                                <i class="fas fa-info-circle" v-on:click="toggleDifference(groupIndex + '-' + questionIndex + '_difference')"></i>
                            </div>
                        </div>
                        <p :id="groupIndex + '-' + questionIndex + '_question'" class="explanation hide col-sm-12 allowNewLine">{{question.explanation}}</p>
                        <div class="hide row" :id="groupIndex + '-' + questionIndex + '_difference'">
                            <div class="col-md-3">
                                <p class="col">Examinator:</p>
                                <div class="col" v-for="(value, key) in instances[0].answers.groups[groupIndex].answers[questionIndex]">
                                    <span>{{key}}:</span>
                                </div>
                                <div class="col">
                                    <span>Final comment:</span>
                                    <span class="col-md-3" >{{results.groups[groupIndex].answers[questionIndex].comment}}</span>
                                </div>
                            </div>
                            <div class="col-md-3" v-for="difference in instances">
                                <p class="col">{{difference.examiner.username}}</p>
                                <div class="col" v-for="(value, key) in difference.answers.groups[groupIndex].answers[questionIndex]">
                                    <input type="checkbox" v-if="key != 'comment'" :checked="value" disabled>
                                    <div class="allowNewLine" style="max-height: 200px; overflow-y:auto;" v-if="key == 'comment'">{{value}}</div>
                                </div>
                            </div>
                            <div class="col-md-3 question-answer">
                                <p class="col">Confirm</p>

                                Y<input type="radio" :name="'confirm-' + groupIndex + '-' + questionIndex" value="1" :checked="checkAnswer(groupIndex, questionIndex)" disabled>
                                N<input type="radio" :name="'confirm-' + groupIndex + '-' + questionIndex" value="0" :checked="!checkAnswer(groupIndex, questionIndex)" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <table class="rightSide" >
            <tr>
                <th>Exam Name</th>
            </tr>
            <tr>
                <td>{{examName}}</td>
            </tr>
            <tr>
                <th>Exam Mark</th>
            </tr>
            <tr>
                <td>{{result.mark}}</td>
            </tr>
            <tr>
                <th>Final Exam Comment</th>
            </tr>
            <tr>
                <td>"Final Exam Comment placeholder"</td>
            </tr>
            <tr>
                <router-link :to="{ name: 'students'}" ><td>Go back<i class="fas fa-long-arrow-alt-left"></i> </td></router-link>
            </tr>
        </table>
    </div>
</template>

<script>
export default {
    name: "examSheet",
    props: ['resultId'],
    data() {
        return {
            result: [],
            error: null,
            groups:  {},
            cesuurs: {},
            instances: null,
            results: null,
            prevQuestionId: null,
            prevCommentId: null,
            instanceId: 0,
            examName: null
       };
    },
    computed: {
        currentUser: {
                get: function () {
                    return this.$store.state.currentUser;
                },
                set: function () {
                }
            },
    },
    methods: {
        checkUser() {
            if (this.currentUser.role_id === 1) {
                this.$router.push({path: '/'});
            }
        },
        getExam(){
            new Promise((res, rej) => {
                axios.get('/api/examsheet/getexam/' + this.instances[0].exam_id)
                .then((response) => {
                    this.groups = JSON.parse(response.data.template)['groups'];
                    this.cesuurs = JSON.parse(response.data.template)['cesuur'];
                })
                .catch((err) =>{
                    //alert('Exam cannot be pulled.')
                })
            });
        },
        getInstances() {
            new Promise((res, rej) => {
                axios.get('/api/examcombine/getinstances/' + this.instanceId)
                .then((response) => {
                    this.instances = response.data;
                    for (var i = 0; i < this.instances.length; i++) {
                        var lists = this.instances[i];
                        lists.answers = JSON.parse(lists.answers);
                    }
                    this.results = JSON.parse(JSON.stringify(this.instances[0].answers));
                    for (var i = 0; i < this.results['groups'].length; i++) {
                        var group = this.results['groups'][i];
                        for (var r = 0; r < group.answers.length; r++) {
                            var answer = group.answers[r];
                            answer.perceived = this.instances[0].answers.groups[i].answers[r].perceived == this.instances[1].answers.groups[i].answers[r].perceived ? this.instances[1].answers.groups[i].answers[r].perceived : -1;

                            answer.doubt = this.instances[0].answers.groups[i].answers[r].doubt == 1 || this.instances[1].answers.groups[i].answers[r].doubt == 1 ? 1 : 0;

                            var finalAnswerComment = JSON.parse(this.result.data)
                            answer.comment = finalAnswerComment.results.groups[i].answers[r].comment;
                        }
                    }
                    this.getExam();
                })
                .catch((err) =>{
                    alert('Instances cannot be pulled.')
                })
            });
        },
        getResult() {
            new Promise((res, rej) => {
                axios.get('/api/students/getResult/' + this.resultId)
                .then((response) => {
                    this.result = response.data;
                    var instanceId = JSON.parse(this.result.data);
                    this.instanceId = instanceId.instances_id[0];
                    this.getExamName();
                    this.getInstances();
                })
                .catch((err) =>{
                    this.$router.push({path: '/students'});
                })
            });
        },
        getExamName() {
            new Promise((res, rej) => {
                axios.get('/api/students/getExamName/' + this.result.exam_id)
                .then((response) => {
                    this.examName = response.data;
                })
                .catch((err) =>{
                })
            });
        },
        checkDoubt(groupIndex, answerIndex){
            var returnData = false;
            for (var i = 0; i < this.instances.length; i++) {
                var instance = this.instances[i];
                var current = instance.answers.groups[groupIndex].answers[answerIndex]['doubt'];
                if(this.instances[i+1] != undefined){
                    var next = this.instances[i+1].answers.groups[groupIndex].answers[answerIndex]['doubt'];
                    if(current == next && current == 1){
                        returnData = true;
                    }else{
                        returnData = false;
                    }
                }
            }
            return returnData;
        },
        checkAnswer(groupIndex, answerIndex){
            var returnData = false;
            for (var i = 0; i < this.instances.length; i++) {
                var instance = this.instances[i];
                var current = instance.answers.groups[groupIndex].answers[answerIndex]['perceived'];
                if(this.instances[i+1] != undefined){
                    var next = this.instances[i+1].answers.groups[groupIndex].answers[answerIndex]['perceived'];
                    if(current == next && current == 1){
                        returnData = true;
                    }else{
                        returnData = false;
                    }
                }
            }
            return returnData;
        },
        toggleQuestion(id){
            document.getElementById(id).classList.toggle('hide');
            if(this.prevQuestionId != null && this.prevQuestionId != id){
                document.getElementById(this.prevQuestionId).classList.add('hide');
            }
            this.prevQuestionId = id;
        },
        toggleDifference(id){
            document.getElementById(id).classList.toggle('hide');
            if(this.prevCommentId != null && this.prevCommentId != id){
                document.getElementById(this.prevCommentId).classList.add('hide');
            }
            this.prevCommentId = id;
        },
    },
    mounted(){
        this.checkUser();
        this.getResult();
    },
}
</script>

<style scoped>
    .page{
        width: 90%;
        margin-left: 5%;
    }

    .error {
        text-align: center;
        color: red;
    }

    .rightSide{
        border: 1px solid #bdbdbd;
        position: fixed;
        right: 0;
        top: 0;
        padding: 10px;
    }

    .leftSide{
        position: relative;
    }

    i{
        cursor: pointer;
    }

    .groups{
        width: 100%;
        display: block;
        margin: 0 auto;
        border: 1px solid black;
        margin-bottom: 25px;
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

    .allowNewLine{
        white-space: pre-line;
    }
</style>
