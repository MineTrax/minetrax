<template>
  <div class="vue-poll">
    <h3
      class="qst dark:text-gray-300"
      v-html="question"
    />
    <div class="ans-cnt">
      <div
        v-for="(a,index) in calcAnswers"
        :key="index"
        :class="{ ans: true, [a.custom_class]: (a.custom_class) }"
      >
        <template v-if="!finalResults">
          <div
            v-if="!visibleResults"
            class="hover:bg-light-blue-100 dark:hover:bg-cool-gray-900"
            :class="{ 'ans-no-vote noselect': true, active: a.selected }"
            @click.prevent="handleVote(a)"
          >
            <span
              class="txt"
              v-html="a.text"
            />
          </div>
          <div
            v-else
            :class="{ 'ans-voted dark:text-gray-200': true, selected: a.selected }"
          >
            <span
              v-if="a.percent"
              class="percent"
              v-text="a.percent"
            />
            <span
              class="txt"
              v-html="a.text"
            />
          </div>

          <span
            class="bg bg-cool-gray-200 dark:bg-cool-gray-700"
            :style="{ width: visibleResults ? a.percent : '0%' }"
          />
        </template>
        <template v-else>
          <div :class="{ 'ans-voted final dark:text-gray-200': true, selected: a.selected }">
            <span
              v-if="a.percent"
              class="percent"
              v-text="a.percent"
            />
            <span
              class="txt"
              v-html="a.text"
            />
          </div>
          <span
            :class="{ 'bg bg-cool-gray-200 dark:bg-cool-gray-700': true, 'bg-light-blue-300 dark:bg-light-blue-500': mostVotes == a.votes }"
            :style="{ width: a.percent }"
          />
        </template>
      </div>
    </div>

    <div
      v-if="isComingSoon"
      class="text-gray-400 text-xs italic"
    >
      {{ __("Poll starting") }}&nbsp;{{ formatTimeAgoToNow(started_at) }}
    </div>

    <div class="flex justify-between items-baseline">
      <div
        v-if="showTotalVotes && (visibleResults || finalResults)"
        class="votes"
        v-text="totalVotesFormatted + ' votes'"
      />
      <div
        v-if="!isComingSoon && closed_at && !finalResults"
        class="text-gray-400 text-xs italic"
      >
        {{ __("Poll closing") }}&nbsp;{{ formatTimeAgoToNow(closed_at) }}
      </div>
    </div>

    <template v-if="!finalResults && !visibleResults && multiple && totalSelections > 0">
      <a
        href="#"
        class="submit"
        @click.prevent="handleMultiple"
        v-text="submitButtonText"
      />
    </template>
  </div>
</template>

<script>
import { useHelpers } from '@/Composables/useHelpers';


export default{
    name: 'Poll',
    props: {
        question: {
            type: String,
            required: false
        },
        answers: {
            type: Array,
            required: false
        },
        showResults: {
            type: Boolean,
            default: false
        },
        showTotalVotes: {
            type: Boolean,
            default: true
        },
        finalResults: {
            type: Boolean,
            default: false
        },
        multiple: {
            type: Boolean,
            default: false
        },
        submitButtonText: {
            type: String,
            default: 'Submit'
        },
        customId: {
            type: Number,
            default: 0
        },
        isComingSoon: {
            type: Boolean,
            default: false
        },
        started_at: {
            type: String,
            default: null
        },
        closed_at: {
            type: String,
            default: null
        }
    },
    setup() {
        const {formatTimeAgoToNow, formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow, formatToDayDateString};
    },
    data(){
        return{
            visibleResults: JSON.parse(this.showResults),
        };
    },
    computed: {
        totalVotes(){
            let totalVotes = 0;
            this.answers.filter(a=>{
                if (!isNaN(a.votes) && a.votes > 0)
                    totalVotes += parseInt(a.votes);
            });
            return totalVotes;
        },
        totalVotesFormatted(){
            return this.totalVotes.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        mostVotes(){
            let max = 0;
            this.answers.filter(a=>{
                if (!isNaN(a.votes) && a.votes > 0 && a.votes >= max)
                    max = a.votes;
            });

            return max;
        },
        calcAnswers(){

            if (this.totalVotes === 0)
                return this.answers.map(a=>{
                    a.percent = '0%';
                    return a;
                });

            //Calculate percent
            return this.answers.filter(a=>{
                if (!isNaN(a.votes) && a.votes > 0)
                    a.percent = ( Math.round( (parseInt(a.votes)/this.totalVotes ) * 100) ) + '%';
                else
                    a.percent =  '0%';

                return a;
            });
        },
        totalSelections(){
            return this.calcAnswers.filter(a => a.selected).length;
        }
    },
    methods: {
        handleMultiple(){

            let arSelected = [];
            this.calcAnswers.filter(a=>{
                if (a.selected){
                    a.votes++;
                    arSelected.push({ value: a.value, votes: a.votes });
                }
            });

            this.visibleResults = true;

            let obj =  { arSelected: arSelected , totalVotes: this.totalVotes };

            if (this.customId)
                obj.customId = this.customId;

            this.$emit('addvote', obj);
        },
        handleVote(a){ //Callback
            if (this.isComingSoon) return;

            if (this.multiple){

                if (a.selected === undefined)
                    console.log('Please add \'selected: false\' on the answer object');

                a.selected = !a.selected;
                return;
            }

            if (this.$page.props.auth.user) {
                a.votes++;
                a.selected = true;
                this.visibleResults = true;
            }

            let obj = { value: a.value, votes: a.votes, totalVotes: this.totalVotes };

            if (this.customId)
                obj.customId = this.customId;

            this.$emit('addvote', obj);
        }
    }
};

</script>

<style>
.vue-poll{
    font-family: 'Avenir', Helvetica, Arial, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    color: #2c3e50;
}

.vue-poll .noselect {
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    -khtml-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

.vue-poll .qst{
    font-weight: normal;
}
.vue-poll .ans-cnt{
    margin: 20px 0;
}
.vue-poll .ans-cnt .ans{
    position: relative;
    margin-top: 10px;
}
.vue-poll .ans-cnt .ans:first-child{
    margin-top: 0;
}

.vue-poll .ans-cnt .ans-no-vote{
    text-align: center;
    border: 1px solid #77C7F7;
    box-sizing: border-box;
    border-radius: 9999px;
    cursor:pointer;
    padding: 5px 0;
    transition: background .2s ease-in-out;
    -webkit-transition: background .2s ease-in-out;
    -moz-transition: background .2s ease-in-out;
}

.vue-poll .ans-cnt .ans-no-vote .txt{
    color: #77C7F7;
    transition: color .2s ease-in-out;
    -webkit-transition: color .2s ease-in-out;
    -moz-transition: color .2s ease-in-out;
}

.vue-poll .ans-cnt .ans-no-vote.active{
    background: #77C7F7;
}

.vue-poll .ans-cnt .ans-no-vote.active .txt{
    color: #fff;
}

.vue-poll .ans-cnt .ans-voted{
    padding: 5px 0;
}

.vue-poll .ans-cnt .ans-voted .percent,
.vue-poll .ans-cnt .ans-voted .txt{
    position: relative;
    z-index: 1;
}
.vue-poll .ans-cnt .ans-voted .percent{
    font-weight: bold;
    min-width: 51px;
    display: inline-block;
    margin:0 10px;
}

.vue-poll .ans-cnt .ans-voted.selected .txt:after{
    content:'✔';
    margin-left: 10px;
}

.vue-poll .ans-cnt .ans .bg{
    position: absolute;
    width: 0%;
    top: 0;
    left: 0;
    bottom: 0;
    z-index: 0;
    border-top-left-radius: 5px;
    border-bottom-left-radius: 5px;
    transition: all .3s cubic-bezier(0.5,1.2,.5,1.2);
    -webkit-transition: all .3s cubic-bezier(0.5,1.2,.5,1.2);
    -moz-transition: all .3s cubic-bezier(0.5,1.2,.5,1.2);
}

.vue-poll .ans-cnt .ans .bg.selected{
    background-color: #77C7F7;
}

.vue-poll .votes{
    font-size: 14px;
    color:#8899A6
}

.vue-poll .submit{
    display: block;
    text-align: center;
    margin: 0 auto;
    max-width: 80px;
    text-decoration: none;
    background-color: #41b882;
    color:#fff;
    padding: 10px 25px;
    border-radius: 5px;

}
</style>
