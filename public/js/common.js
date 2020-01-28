if (window.requestIdleCallback) {
        //console.log('log IDLE start');
        requestIdleCallback(backgroundTask);
} 
// Функция обратного вызова requestIdleCallback
function backgroundTask(deadline) {

//setTimeout(sayHi, 15000);
}
function sayHi() {
    requestIdleCallback(backgroundTask);
    fetch('/index/time/', {
        mode: 'cors',
        method: 'POST',
        cache: 'no-cache',
        credentials: 'same-origin',
    })
//    .then(res => {
//        console.log(res);
//    });
    //console.log('log IDLE');
}
var mvcrbstore = new Vuex.Store({
    state: {
        content: '',
        MainMenu: {},
        User: {}
    },
    mutations: {
        content(state, n) {
//            console.log('n c s',state);
            state.content = n;
        },
        User(state, n) {
//            console.log('store sn',n);
            state.User = n;
        },
        MainMenu(state, n) {
//            console.log('store sn',n);
            state.MainMenu = n;
        }

    }
});

var mvcrbmixin = {
    data() {
        return {
            Count: 0, perPage: 6, currentPage: 1,
            searchStr: '', currentSortDir: 'asc', currentSort: 'id'
        };
    },
    methods: {
        sort(s) {
            if (s === this.currentSort) {
                this.currentSortDir = this.currentSortDir === 'asc' ? 'desc' : 'asc';
            }
            this.currentSort = s;
            this.setPage(this.currentPage);
//            console.log(this.currentSortDir,this.currentSort);
        }
    },
    computed: {
        paginator() {
            let p = _.range(1, Math.ceil(this.Count / this.perPage) + 1);
            let st = this.currentPage - 1;
            if (st < 1)
                st = 1;
            if (st > (p.length-2))
                st = p.length-2;
            return {
                count: p.length,
                currentPage: this.currentPage,
                pages: p.slice(st-1, st +2)
            };
        }
    }
}