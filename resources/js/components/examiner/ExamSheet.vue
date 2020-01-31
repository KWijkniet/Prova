<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <div class="d-flex d-inline align-self-start flex-column d-inline justify-content-around mt-2">
            <div class="card mb-2" v-for="(group, groupIndex) in groups">
                <div class="card-body">
                    <h1>
                        #{{groupIndex + 1}}
                        {{group.name}}
                    </h1>
                    <div class="dropdown group-dropdown">
                        <i class="fas fa-ellipsis-h pointer" :id="'group-' + groupIndex" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                        <div class="dropdown-menu dropdown-menu-right" :aria-labelledby="'group-' + groupIndex">
                            <p class="dropdown-item pointer" data-toggle="collapse" :data-target="'#group' + groupIndex" aria-expanded="false" :aria-controls="'#group' + groupIndex">Toggle</p>
                        </div>
                    </div>
                    <div class="table-responsive" v-bind:class="[groupIndex == 0 ? 'show' : 'collapse']" :id="'group' + groupIndex">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 25px">Mark</th>
                                    <th style="width: 25px"></th>
                                    <th style="min-width: 200px;">Question</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(question, questionIndex) in group.questions">
                                    <td>
                                        <b>{{cesuurs.marks[question.requiredMark]}}</b>
                                    </td>
                                    <td>
                                        <i v-on:click="toggleExplanation(groupIndex + '-' + questionIndex + '_question')" class="fas pointer fa-info-circle"></i>
                                        <i v-on:click="toggleComment('comment-' + groupIndex + '-' + questionIndex)" class="fas pointer fa-comment"></i>
                                    </td>
                                    <td>
                                        <div>
                                            {{question.question}}
                                        </div>
                                        <div :id="groupIndex + '-' + questionIndex + '_question'" class="hide" style="width: 100%;">
                                            <hr />
                                            {{question.explanation}}
                                        </div>
                                            <textarea :id="'comment-' + groupIndex + '-' + questionIndex" @blur="saveAnswers()" v-model="savedAnswers.groups[groupIndex].answers[questionIndex].comment" v-on:keydown.enter.prevent="allowNewLine('comment-' + groupIndex + '-' + questionIndex)" rows="6" oninput='this.style.height = "";this.style.height = this.scrollHeight + "px"' class="form-control hide" style="resize:none;"></textarea>
                                    </td>
                                    <td>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" :id="'perceived-' + groupIndex + '-' + questionIndex" @blur="saveAnswers()" v-model="savedAnswers.groups[groupIndex].answers[questionIndex].perceived">
                                            <label class="custom-control-label" :for="'perceived-' + groupIndex + '-' + questionIndex">perceived</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" :id="'doubt-' + groupIndex + '-' + questionIndex" @blur="saveAnswers()" v-model="savedAnswers.groups[groupIndex].answers[questionIndex].doubt">
                                            <label class="custom-control-label" :for="'doubt-' + groupIndex + '-' + questionIndex">doubt</label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button class="btn btn-outline-primary" style="float:right" v-on:click="submitAnswers()"><i class="fas fa-save"></i> Submit</button>
                </div>
            </div>
        </div>
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
        getInstance(){
            var self = this;
            new Promise((res, rej) => {
                axios.post('/api/examsheet/getinstance', {id: this.id, examiner_id: this.currentUser.id})
                .then((response) => {
                    this.instance = response.data;
                    if(this.instance == null){
                        this.$noty.error("Access Denied")
                        self.$router.push({path :'/'});
                    }else{
                        if(this.instance['answers'] && this.instance['answers'].length > 0) {
                            this.savedAnswers = JSON.parse(this.instance['answers']);
                        }
                        this.getExam();
                    }
                })
                .catch((err) => {
                    self.createLog(self.currentUser, `Tried to load instance but an error occured`);
                    self.$noty.error("Cannot load instance.");
                })
            });
        },
        getExam(){
            var self = this;
            new Promise((res, rej) => {
                axios.get('/api/examsheet/getexam/' + this.instance.exam_id)
                .then((response) => {
                    this.groups = JSON.parse(response.data['template'])['groups'];
                    this.cesuurs = JSON.parse(response.data['template'])['cesuur'];
                    this.examName = response.data['name'];

                    var groups = [];
                    for(var i = 0; i < this.groups.length; i++){
                        var group = this.groups[i];

                        if(this.savedAnswers == null){
                            groups.push({"name" : group.name,"answers" : []});
                        }

                        for(var r = 0; r < group.questions.length; r++){
                            var question = group.questions[r];
                            question['show'] = false;

                            if(this.savedAnswers == null){
                                var data = {
                                    perceived : false,
                                    doubt : false,
                                    comment : ''
                                };

                                groups[i].answers[r] = data;
                            }
                        }
                    }

                    if(this.savedAnswers == null){
                        this.savedAnswers =  {"groups" : groups};
                    }
                })
                .catch((err) =>{
                    self.createLog(self.currentUser, `Tried to load exam but an error occured`);
                    self.$noty.error("Cannot load exam.");
                })
            });
        },
        allowNewLine(id) {
            var elem = document.getElementById(id);
            elem.value = elem.value + "\n";
            return false;
        },
        toggleExplanation(id){
            document.getElementById(id).classList.toggle('hide');
            if(this.prevExplanationId != null && this.prevExplanationId != id){
                document.getElementById(this.prevExplanationId).classList.add('hide');
            }
            this.prevExplanationId = id;
        },
        toggleComment(id){
            document.getElementById(id).classList.toggle('hide');
            if(this.prevCommentId != null && this.prevCommentId != id){
                document.getElementById(this.prevCommentId).classList.add('hide');
            }
            this.prevCommentId = id;
            document.getElementById(id).focus();
        },
        saveAnswers(){
            var self = this;
            new Promise((res, rej) => {
                axios.post('/api/examsheet/saveanswers',{'instance_id' : this.instance.id ,'answers': JSON.stringify(this.savedAnswers)})
                .then((response) => {})
                .catch((err) =>{
                    self.$noty.error("Cannot save answers");
                })
            });
        },
        submitAnswers(){
            var self = this;
            new Promise((res, rej) => {
                axios.post('/api/examsheet/saveresults',{'instance_id' : this.instance.id ,'answers': JSON.stringify(this.savedAnswers)})
                .then((response) => {
                    self.$noty.success("Succesfully submitted answers.");
                    if(response.data.status == 'success'){
                        self.checkCombine();
                    }else{
                        self.$router.push({name: 'ExaminerHome'});
                    }
                })
                .catch((err) =>{
                    self.createLog(self.currentUser, `Tried to save answers but an error occured`);
                    self.$noty.error("Cannot save answers.");
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
                            self.$router.push({name: 'ExamCombine', params:{'instanceId': this.instance.id}});
                        }
                    })
                    .catch((err) =>{
                        self.$noty.error("Cannot check for combine");
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
.card{
    width: 100%;
}

.card-container{
    flex-wrap: wrap;
}

.dropdown-item{
    margin-bottom: 0px;
}

.text-center{
    text-align: center;
}

.card-body{
    position: relative;
}

.group-dropdown{
    position: absolute;
    right: 20px;
    top: 20px;
}

@media (max-width: 426px) {
    .card-container{
        width: 100%;
        margin: 0;
    }
}

</style>
