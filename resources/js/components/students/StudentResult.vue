<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>

        <div class="d-flex flex-row d-inline align-items-start align-self-start justify-content-around mt-2 card-container">
            <div class="card-large d-flex align-self-start flex-column d-inline justify-content-around">
                <div class="card mb-2 group" v-for="(group, groupIndex) in groups">
                    <div class="card-body">
                        <h1 style="display: inline-block;">
                            #{{groupIndex + 1}}
                            {{groups[groupIndex].name}}
                        </h1>
                        <div class="dropdown group-dropdown">
                            <i class="fas fa-ellipsis-h pointer" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                                <p class="dropdown-item pointer" data-toggle="collapse" :data-target="'#group' + groupIndex" aria-expanded="false" :aria-controls="'#group' + groupIndex">Toggle</p>
                            </div>
                        </div>
                        <div class="table-responsive" v-bind:class="[groupIndex == 0 ? 'show' : 'collapse']" :id="'group' + groupIndex">
                            <table class="table table-hover table-large">
                                <thead>
                                    <tr>
                                        <th style="width: 59px">Required Mark</th>
                                        <th style="width: 25px;"></th>
                                        <th>Question</th>
                                        <th style="width: 25px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(question, questionIndex) in group.questions">
                                        <tr>
                                            <td>
                                                {{cesuurs.marks[question.requiredMark]}}
                                            </td>
                                            <td>
                                                <i v-on:click="toggleQuestion(groupIndex + '-' + questionIndex + '_question')" class="fas fa-info-circle pointer"></i>
                                                <i class="fas fa-eye pointer" v-on:click="toggleOpened(groupIndex, questionIndex)"></i>
                                            </td>
                                            <td>
                                                {{question.question}}
                                                <div :id="groupIndex + '-' + questionIndex + '_question'" class="hide" style="width: 100%;">
                                                    <hr />
                                                    {{question.explanation}}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" :id="groupIndex + '-' + questionIndex + '_perceived'" :checked="checkAnswer(groupIndex, questionIndex)" disabled>
                                                    <label class="custom-control-label" :for="groupIndex + '-' + questionIndex + '_perceived'">perceived</label>
                                                </div>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" :id="groupIndex + '-' + questionIndex + '_doubt'" :checked="checkDoubt(groupIndex, questionIndex)" disabled>
                                                    <label class="custom-control-label" :for="groupIndex + '-' + questionIndex + '_doubt'">doubt</label>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr v-if="openedId == (groupIndex+'-'+questionIndex)">
                                            <td colspan="3"></td>
                                            <td>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" class="custom-control-input" :name="'confirm-' + groupIndex + '-' + questionIndex" :id="'confirm-' + groupIndex + '-' + questionIndex" value="1" v-on:click="updateConfirm(groupIndex, questionIndex, $event.target)" :checked="checkAnswer(groupIndex, questionIndex) == true" disabled>
                                                    <label class="custom-control-label" :for="'confirm-' + groupIndex + '-' + questionIndex">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" class="custom-control-input" :name="'confirm-' + groupIndex + '-' + questionIndex" :id="'denied-' + groupIndex + '-' + questionIndex" value="0" v-on:click="updateConfirm(groupIndex, questionIndex, $event.target)" :checked="checkAnswer(groupIndex, questionIndex) == false" disabled>
                                                    <label class="custom-control-label" :for="'denied-' + groupIndex + '-' + questionIndex">No</label>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr v-for="(difference, differenceIndex) in instances" v-if="openedId == (groupIndex+'-'+questionIndex)">
                                            <td colspan="2">
                                                {{difference.examiner.username}}
                                            </td>
                                            <td colspan="2">
                                                <div class="col" v-for="(value, key) in difference.answers.groups[groupIndex].answers[questionIndex]">
                                                    <div class="custom-control custom-checkbox" v-if="key != 'comment'">
                                                        <input type="checkbox" class="custom-control-input" :id="groupIndex + '-' + questionIndex + '-' + differenceIndex + '_' + key" :checked="value" disabled>
                                                        <label class="custom-control-label" :for="groupIndex + '-' + questionIndex + '-' + differenceIndex + '_' + key">{{key}}</label>
                                                    </div>

                                                    <div class="form-group" v-if="key == 'comment'">
                                                        <label :for="groupIndex + '-' + questionIndex + '-' + differenceIndex + '_' + key">Comment:</label>
                                                        <textarea :id="groupIndex + '-' + questionIndex + '-' + differenceIndex + '_' + key" class="form-control allowNewLine" rows="5" :value="value" readonly disabled></textarea>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card card-small" v-if="exam != null">
                <div class="card-body">
                    <h5 class="card-title mt-2">
                        <b>Details:</b>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-small">
                            <tbody>
                                <tr>
                                    <th>Exam Name:</th>
                                    <td>{{exam.name}}</td>
                                </tr>
                                <tr>
                                    <th>Mark:</th>
                                    <td>{{result.mark}}</td>
                                </tr>
                                <tr>
                                    <th>Comment:</th>
                                    <td>{{finalComment}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
            instanceId: 0,
            openedId: "",
            exam: null,
            finalComment: null
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
        toggleOpened(groupIndex, questionIndex){
            if(this.openedId == groupIndex + '-' + questionIndex){
                this.openedId = "";
            }else{
                this.openedId = groupIndex + '-' + questionIndex;
            }
        },
        getExam(){
            var self = this;
            new Promise((res, rej) => {
                axios.get('/api/examsheet/getexam/' + this.instances[0].exam_id)
                .then((response) => {
                    this.exam = response.data;
                    this.groups = JSON.parse(response.data.template)['groups'];
                    this.cesuurs = JSON.parse(response.data.template)['cesuur'];
                })
                .catch((err) =>{
                    self.createLog(self.currentUser, `Tried to load exam but an error occured`);
                    self.$noty.error("Exam cannot be pulled");
                })
            });
        },
        getInstances() {
            var self = this;
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
                    self.createLog(self.currentUser, `Tried to load instances but an error occured`);
                    self.$noty.error("Cannot load instances.");
                })
            });
        },
        getResult() {
            var self = this;
            new Promise((res, rej) => {
                axios.get('/api/students/getResult/' + this.resultId)
                .then((response) => {
                    this.result = response.data;
                    var instanceId = JSON.parse(this.result.data);
                    this.instanceId = instanceId.instances_id[0];
                    this.finalComment = JSON.parse(this.result.data)['finalComment'];
                    this.getInstances();
                })
                .catch((err) =>{
                    this.$router.push({path: '/students'});
                    self.createLog(self.currentUser, `Tried to load students results but an error occured`);
                    self.$noty.error("Cannot load students results.");
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
        back(){
            this.$router.go(-1);
        }
    },
    mounted(){
        this.getResult();
    },
}
</script>

<style scoped>
.container{
    max-width: 90%;
}

.card-container{
    flex-wrap: wrap;
}

.card-large{
    width: 70%;
}

.card-small{
    width: 29%;
    margin-left: 1%;
}

.dropdown-item{
    margin-bottom: 0px;
}

.text-center{
    text-align: center;
}

.not-allowed:hover, .not-allowed{
    cursor: not-allowed !important;
    color: gray;
}

.card-body{
    position: relative;
}

.group-dropdown{
    position: absolute;
    right: 20px;
    top: 30px;
}

@media (max-width: 426px) {
    .card{
        width: 100%;
    }

    .card-large{
        width: 100%;
    }

    .card-small{
        margin-left: 0px;
    }

    .card-container{
        flex-wrap: wrap-reverse;
    }
}
</style>
