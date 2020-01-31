<template>
    <div class="container">
        <p v-if="error">{{error}}</p>

        <div class="d-flex flex-row d-inline align-items-start align-self-start justify-content-around mt-2 card-container">
            <div class="card-large d-flex align-self-start flex-column d-inline justify-content-around">
                <div class="card mb-2" v-for="(group, groupIndex) in groups">
                    <div class="card-body">
                        <h1 style="display: inline-block;">
                            #{{groupIndex + 1}}
                            {{group.name}}
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
                                        <th style="width: 25px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="(question, questionIndex) in group.questions" v-if="filterString.length <= 0 || filterString.includes(question.conflict)">
                                        <tr>
                                            <td>
                                                {{cesuurs.marks[question.requiredMark]}}
                                            </td>
                                            <td>
                                                <i v-on:click="toggleQuestion(groupIndex + '-' + questionIndex + '_question')" class="fas pointer fa-info-circle"></i>
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
                                            <td>
                                                <i class="fas pointer" v-bind:class="question.icon" v-on:click="toggleOpened(groupIndex, questionIndex)"></i>
                                            </td>
                                        </tr>

                                        <tr v-if="openedId == (groupIndex+'-'+questionIndex)">
                                            <td colspan="3"></td>
                                            <td>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" class="custom-control-input" :name="'confirm-' + groupIndex + '-' + questionIndex" :id="'confirm-' + groupIndex + '-' + questionIndex" value="1" v-on:click="updateConfirm(groupIndex, questionIndex, $event.target)" :checked="checkAnswer(groupIndex, questionIndex) == true" :disabled="isLocked(groupIndex, questionIndex)">
                                                    <label class="custom-control-label" :for="'confirm-' + groupIndex + '-' + questionIndex">Yes</label>
                                                </div>
                                                <div class="custom-control custom-radio">
                                                    <input type="radio" class="custom-control-input" :name="'confirm-' + groupIndex + '-' + questionIndex" :id="'denied-' + groupIndex + '-' + questionIndex" value="0" v-on:click="updateConfirm(groupIndex, questionIndex, $event.target)" :checked="checkAnswer(groupIndex, questionIndex) == false" :disabled="isLocked(groupIndex, questionIndex)">
                                                    <label class="custom-control-label" :for="'denied-' + groupIndex + '-' + questionIndex">No</label>
                                                </div>
                                            </td>
                                            <td></td>
                                        </tr>

                                        <tr v-for="(difference, differenceIndex) in instances" v-if="openedId == (groupIndex+'-'+questionIndex)">
                                            <td colspan="2">
                                                {{difference.examiner.username}}
                                            </td>
                                            <td colspan="3">
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

            <div class="card card-small">
                <div class="card-body">
                    <h5 class="card-title mt-2">
                        <b>Comment:</b>
                    </h5>
                    <div class="table-responsive">
                        <div class="form-group">
                            <textarea class="form-control allowNewLine" v-model="finalComment"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-small">
                <div class="card-body">
                    <h5 class="card-title mt-2">
                        <b>Filters:</b>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-small">
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" id="filter0" name="filter" v-on:click="filter(0)" checked="true">
                                            <label class="custom-control-label" for="filter0">None</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" id="filter1" name="filter" v-on:click="filter(1)">
                                            <label class="custom-control-label" for="filter1">No Conflict</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" id="filter2" name="filter" v-on:click="filter(2)">
                                            <label class="custom-control-label" for="filter2">Conflicts</label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" id="filter3" name="filter" v-on:click="filter(3)">
                                            <label class="custom-control-label" for="filter3">Doubts</label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button class="btn btn-outline-primary mt-2" type="button" name="button" v-on:click="confirmExam()"><i class="fas fa-save"></i> Submit Exam</button>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    name: "examSheet",
    props: ['instanceId'],
    data() {
        return {
            error: null,
            groups:  {},
            cesuurs: {},
            instances: null,
            results: null,
            prevQuestionId: null,
            conflictCount: 0,
            openedId: "",
            filterString: [],
            finalComment: ""
       };
    },
    computed: {
        currentUser: {
            get: function () {
                return this.$store.state.currentUser;
            },
            set: function () {
            }
        }
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
                    self.groups = JSON.parse(response.data.template)['groups'];
                    self.cesuurs = JSON.parse(response.data.template)['cesuur'];

                    for(var i = 0; i < self.groups.length; i++){
                        var group = self.groups[i];
                        for(var r = 0; r < group.questions.length; r++){
                            var question = group.questions[r];
                            self.$set(self.groups[i].questions[r], 'icon', '');
                            self.$set(self.groups[i].questions[r], 'show', 'false');


                            if(self.results.groups[i].answers[r].perceived01 == self.results.groups[i].answers[r].perceived02 && (self.results.groups[i].answers[r].doubt01 || self.results.groups[i].answers[r].doubt02)){
                                //both conflicts
                                question.icon = "fa-exclamation-triangle";
                                question.conflict = "conflict and doubt";
                            }
                            else if(self.results.groups[i].answers[r].doubt01 || self.results.groups[i].answers[r].doubt02){
                                //doubt selected
                                question.icon = "fa-exclamation";
                                question.conflict = "doubt";
                            }
                            else if(self.results.groups[i].answers[r].perceived01 != self.results.groups[i].answers[r].perceived02){
                                //perceived conflict
                                question.icon = "fa-exclamation-triangle";
                                question.conflict = "conflict";
                            }else{
                                question.icon = "fa-info-circle";
                                question.conflict = "none";
                            }
                        }
                    }
                    self.filter(0);
                })
                .catch((err) =>{
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
                            answer.perceived01 = this.instances[0].answers.groups[i].answers[r].perceived;
                            answer.perceived02 = this.instances[1].answers.groups[i].answers[r].perceived;

                            answer.doubt01 = this.instances[0].answers.groups[i].answers[r].doubt;
                            answer.doubt02 = this.instances[1].answers.groups[i].answers[r].doubt;
                            answer.comment = "";

                            if((answer.perceived01 != answer.perceived02) || (answer.doubt01 || answer.doubt02)){
                                this.conflictCount++;
                            }
                        }
                    }
                    this.getExam();
                })
                .catch((err) =>{
                    self.$noty.error("Instances cannot be pulled");
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
                    }else if(current == next && current == 0){
                        returnData = false;
                    }else{
                        return null;
                    }
                }
            }
            return returnData;
        },
        allowNewLine(id) {
            var elem = document.getElementById(id);
            elem.value = elem.value + "\n";
            return false;
        },
        updateConfirm(groupIndex, answerIndex, elem){
            this.conflictCount--;

            //change icon from ...  to no conflicts
            this.groups[groupIndex].questions[answerIndex].icon = "fa-info-circle";

            if(elem.value == 1){
                this.results['groups'][groupIndex].answers[answerIndex]['perceived'] = 1;
            }else{
                this.results['groups'][groupIndex].answers[answerIndex]['perceived'] = 0;
            }
        },
        toggleQuestion(id){
            document.getElementById(id).classList.toggle('hide');
            if(this.prevQuestionId != null && this.prevQuestionId != id){
                document.getElementById(this.prevQuestionId).classList.add('hide');
            }
            this.prevQuestionId = id;
        },
        filter(type){
            switch (type) {
                case 0:
                    this.filterString = [];
                    break;
                case 1:
                    this.filterString = ["none"];
                    break;
                case 2:
                    this.filterString = ["conflict","conflict and doubt"];
                    break;
                case 3:
                    this.filterString = ["doubt","conflict and doubt"];
                    break;
                default:
                    break;
            }
        },
        confirmExam(){
            var self = this;

            if(this.conflictCount > 0){
                this.$dialog
                .confirm({
                    title: "Warning",
                    body: "You have " + this.conflictCount + " possible unsolved conflicts. Are you sure you want to submit this exam?\nyou CAN'T undo this action",
                })
                .then(dialog => {
                    confirmExam();
                })
                .catch(() => {
                    return;
                });
            }else{
                confirmExam();
            }
            function confirmExam(){
                var mark = self.calculateMark();

                //create row in results table.
                var data = {
                    "instances_id": [],
                    "results": self.results,
                    "finalComment": self.finalComment
                };

                for (var i = 0; i < self.instances.length; i++) {
                    data.instances_id.push(self.instances[i].id);
                }

                var dataToUpload = {
                    "exam_id": self.instances[0].exam_id,
                    "student_id": self.instances[0].student_id,
                    "mark": mark,
                    "data": JSON.stringify(data),
                }

                new Promise((res, rej) => {
                    axios.post('/api/examcombine/createresult', {data: dataToUpload})
                    .then((response) => {
                        self.$noty.info(dataToUpload['mark']);
                        self.$router.push({ path: '/' });
                    })
                    .catch((err) =>{
                        self.$noty.error("An error occured");
                    })
                });
            }
        },
        calculateMark(){
            //preset points to 0
            var calcData = {};
            for (var i = 0; i < this.cesuurs.marks.length; i++) {
                calcData[this.cesuurs.marks[i]] = 0;
            }

            //calculate points
            for (var i = 0; i < this.results.groups.length; i++) {
                var group = this.results.groups[i];
                for (var r = 0; r < group.answers.length; r++) {
                    var answer = group.answers[r];
                    if(answer.perceived){
                        calcData[this.cesuurs.marks[this.groups[i].questions[r].requiredMark]]++;
                    }
                }
            }

            //calculate statement
            for (var i = 0; i < this.cesuurs.calculations.length; i++) {
                for (var r = 0; r < this.cesuurs.marks.length; r++) {
                    var string = '[' + this.cesuurs.marks[r] + ']';
                    this.cesuurs.calculations[i].calculation = this.cesuurs.calculations[i].calculation.split(string).join(calcData[this.cesuurs.marks[r]]);
                }
            }

            //calculate mark
            for (var i = 0; i < this.cesuurs.calculations.length; i++) {
                if(eval(this.cesuurs.calculations[i].calculation)){
                    return this.cesuurs.calculations[i].name;
                }
            }
            return "Cesuur has a calculation error";
        },
        isLocked(groupIndex, questionIndex){
            var returnData = false;
            for (var i = 0; i < this.instances.length; i++) {
                var instance = this.instances[i];
                var current = instance.answers.groups[groupIndex].answers[questionIndex]['perceived'];
                var currentDoubt = instance.answers.groups[groupIndex].answers[questionIndex]['doubt'];
                if(this.instances[i+1] != undefined){
                    var next = this.instances[i+1].answers.groups[groupIndex].answers[questionIndex]['perceived'];
                    var nextDoubt = this.instances[i+1].answers.groups[groupIndex].answers[questionIndex]['doubt'];
                    if(currentDoubt == 0 && nextDoubt == 0){
                        if((current == next && current == 1) || (current == next && current == 0)){
                            returnData = true;
                        }else{
                            returnData = false;
                        }
                    }else{
                        returnData = false;
                    }
                }
            }
            return returnData;
        }
    },
    mounted(){
        this.getInstances();
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
