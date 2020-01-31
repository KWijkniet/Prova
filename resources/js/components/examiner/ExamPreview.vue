<template>
    <div class="container">
        <p v-if="error">{{error}}</p>
        <button class="btn btn-outline-primary mt-2" v-on:click="back()"><i class="fas fa-long-arrow-alt-left"></i> Back</button>

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
                                        <div class="questionExplanation">
                                            <i v-on:click="toggleExplanation(groupIndex + '-' + questionIndex + '_question')" class="fas pointer fa-info-circle" style="float:left;" ></i>
                                            {{question.question}}
                                        </div>
                                        <div :id="groupIndex + '-' + questionIndex + '_question'" class="hide" style="width: 100%;">
                                            <br />
                                            {{question.explanation}}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" disabled>
                                            <label class="custom-control-label">perceived</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" disabled>
                                            <label class="custom-control-label">doubt</label>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script scoped>
export default {
    name: "examPreview",
    props: ['id'],
    data() {
        return {
            error: null,
            groups:  {},
            cesuurs: {},
            prevQuestionId: null,
            prevCommentId: null
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
        back(){
            this.$router.go(-1);
        },
        checkUser() {
            if (this.currentUser.role_id === 1) {
                this.$router.push({path: '/admin'});
            }
        },
        getExam(){
            var self = this;
            new Promise((res, rej) => {
                axios.get('/api/examsheet/getexam/' + this.id)
                .then((response) => {
                    self.groups = JSON.parse(response.data.template)['groups'];
                    self.cesuurs = JSON.parse(response.data.template)['cesuur'];
                })
                .catch((err) => {
                    self.$noty.error("Cannot get exam");
                })
            });
        },
        toggleExplanation(id){
            document.getElementById(id).classList.toggle('hide');
            document.getElementById(id).focus();
            if(this.prevExplanationId != null && this.prevExplanationId != id){
                document.getElementById(this.prevExplanationId).classList.add('hide');
            }
            this.prevExplanationId = id;
        }
    },
    mounted(){
        this.checkUser();
        this.getExam();
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
