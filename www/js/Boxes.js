
function Class() { }

Class.prototype.construct = function() {};

Class.__asMethod__ = function(func, superClass) {    
    return function() {
        var currentSuperClass = this.Super;
        this.Super = superClass;
        var ret = func.apply(this, arguments);        
        this.Super = currentSuperClass;
        return ret;
    };
};

Class.extend = function(def) {
    var classDef = function() {
        if (arguments[0] !== Class && this.construct) { this.construct.apply(this, arguments); }
    };
    
    var proto = new this(Class);
    var superClass = this.prototype;
    
    for (var n in def) {
        var item = def[n];                        
        
        if (item instanceof Function) {
            item = Class.__asMethod__(item, superClass);
        }
        
        proto[n] = item;
    }

    proto.Super = superClass;
    classDef.prototype = proto;    
    classDef.extend = this.extend;
    return classDef;
};

var boxCollection = new Array();

/**
 * 
 */
var BoxController = Class.extend({
    
    /**
     * @var String DOM id of the container div 
     */
    boxId: '',
    

    /**
     * @var DataProvider
     */
    dataProvider: new DataProvider(),
    
    /**
     * @var Object To store data from ajax responces
     */
    data: null,
    
    /**
     * @var String Name of the requested resource, used in Ajax URL
     */
    endpoint: null,
    
    construct: function () {},
    
    init: function () {
        //this.getContentDom().children().hide();
        if (this.boxId && this.getContentDom().length) {
            this.loadData();
        }
    },
    
    /**
     * @return jQuery DOM element which holds the box
     */
    getBoxDom: function () {
        return $('#' + this.boxId);
    },

    /**
     * @return jQuery DOM element which holds content of the box
     */
    getContentDom: function () {
        if (!this._contentDom) {
            this._contentDom = $('#' + this.boxId + ' .box-content:first');
        }
        return this._contentDom;
    },
    
    /**
     * @return jQuery DOM element which holds header of the box
     */
    getHeaderDom: function () {
        return $('#' + this.boxId + ' .box-header:first');
    },
    
    getLoaderHtml: function () {
        return $('#ajax-loader-holder').html();
    },
    
    
    /**
     * @return String
     */
    getBoxId: function () {
        return this.boxId
    },
    
    beforeLoadData: function () {
        this.getContentDom().children().hide();
        this.getContentDom().append(this.getLoaderHtml());
    },
    
    afterLoadData: function () {
        this.getContentDom().find('.ajax-loader').remove();
        this.getContentDom().children(':first').show();
    },
    
    /**
     * Will handle Ajax response of the loadData
     */
    loadDataCallback: function () {
        this.boxController.afterLoadData();
    },
    
    /**
     * Load Data by Ajax
     */
    loadData: function () {
        this.beforeLoadData();
        if (!this.loadDataCallback.boxController) {
            this.loadDataCallback.boxController = this;
        }
        this.data = null;
        this.dataProvider.setEndpoint(this.endpoint);
        this.dataProvider.setCallback(this.loadDataCallback);
        this.data = this.dataProvider.fetch();
    }
    
});

var GraphBoxController = BoxController.extend({
    
    getGraphHolder: function () {
        return $('#' + this.boxId + '-graph-holder'); 
    },
    
    init: function () {
        this.getContentDom().children().hide();
        this.getContentDom().parent().find('.box-header-button-show-graph').click(function () {
            var box = $(this).parents('.box:first');
            var dataGrid = box.find('.data-grid-holder');
            if (dataGrid.css('display') != 'none') {
                dataGrid.hide();
                box.find('.graph-holder').show();
                $(window).resize();
            } else {
                box.find('.graph-holder').hide();
                dataGrid.show();
            }
            return false;
        });
        if (this.getContentDom().length) {
            this.loadData();
        }
    },
    
    afterLoadData: function () {
        this.getContentDom().find('.ajax-loader').remove();
        this.getContentDom().children(':first').show();
        this.prepareGraph();
    }
});

var BC_KeywordsAnalysis = GraphBoxController.extend({

    /**
     * @var String DOM id of the container div 
     */
    boxId: 'box-keywords-analysis',
    
    /**
     * @var String Name of the requested resource, used in Ajax URL
     */
    endpoint: 'keywords',
    
    
    prepareGraph: function () {
        if (!this.data) {
            return;
        }
        
        var graphHolder = this.getGraphHolder();
        
        var graphHolderId = graphHolder.attr('id');
        
        var options = {
            chart: {
                renderTo: graphHolderId,
                margin: [10, 10, 10, 10],
                animation: false,
                defaultSeriesType: 'pie'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                       enabled: false
                    },
                    showInLegend: true
                }
            },
            events: {
                load: function (e) {
                    var container = $(this.container);
                    this.setSize(container.width(), container.height());
                }
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
                }
            },
            legend: {
                borderRadius: 0
            },
            series: [{
                 type: 'pie',
                 name: 'Browser share',
                 data: new Array()
             }]
        };
        for (var i = 0; i < this.data.keywords.length; i++) {
            options.series[0].data.push(new Array(
                this.data.keywords[i].keyword,
                this.data.keywords[i].percent
            ));
        }
        
        this.graph = new Highcharts.Chart(options);
    },
    
    loadDataCallback: function (data, textStatus, jqXHR) {
        var boxController = this.success.boxController;
        boxController.data = data;
        var table = boxController.getContentDom().find('.data-grid-holder > table');
        var trTemplate = table.find('tbody tr').clone();
        var tr = null;
        table.find('tbody tr').remove();
        for (var i = 0; i < boxController.data.keywords.length; i++) {
            tr = trTemplate.clone();
            for (n in boxController.data.keywords[i]) {
                var value = boxController.data.keywords[i][n];
                if (n == 'percent') {
                    value = value + '%';
                } 
                tr.find('td.col-' + n).text(value);
            }
            table.find('tbody').append(tr);
        }
        boxController.afterLoadData();
    },
    
    construct: function () {}
    
});

var BC_ReviewSites = GraphBoxController.extend({

    /**
     * @var String DOM id of the container div 
     */
    boxId: 'box-review-sites',
    
    /**
     * @var String Name of the requested resource, used in Ajax URL
     */
    endpoint: 'sites',
    
    prepareGraph: function () {
        if (!this.data) {
            return;
        }
        
        var graphHolderId = this.boxId + '-graph-holder';
        
        var graphHolder = $('#' + graphHolderId);
        
        var options = {
            chart: {
                renderTo: graphHolderId,
                type: 'bar'
            },
            title: {
                text: this.getHeaderDom().find('.box-header-title').text()
            },
            colors: [
                '#80699B', 
                '#AA4643', 
                '#4572A7', 
                '#89A54E', 
                '#3D96AE', 
                '#DB843D', 
                '#92A8CD', 
                '#A47D7C', 
                '#B5CA92'
            ],
            xAxis: {
                categories: [],
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: this.getHeaderDom().find('.box-header-title').text(),
                    align: 'high'
                }
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            legend: {
                verticalAlign: 'bottom',
                borderWidth: 1,
                borderRadius: 0,
                backgroundColor: '#FFFFFF',
                shadow: true
            },
            credits: {
                enabled: false
            },
            series: [{
                name: 'Average',
                data: []
            }, {
                name: 'Negative',
                data: []
            }, {
                name: 'Neutral',
                data: []
            }, {
                name: 'Positive',
                data: []
            }]
        }
        
        for (var i = 0; i < this.data.sites.length; i++) {
            var site = this.data.sites[i];
            options.xAxis.categories.push(site.site);
            options.series[0].data.push(site.average);
            options.series[1].data.push(site.negative);
            options.series[2].data.push(site.neutral);
            options.series[3].data.push(site.positive);
        }
        
        this.graph = new Highcharts.Chart(options);
    },
    
    loadDataCallback: function (data, textStatus, jqXHR) {
        var boxController = this.success.boxController;
        boxController.data = data;
        var table = boxController.getContentDom().find('.data-grid-holder > table');
        var trTemplate = table.find('tbody tr').clone();
        var tr = null;
        var trFooter = table.find('tfoot tr');
        trFooter.find('th:not(:first)').text('0');
        table.find('tbody tr').remove();
        for (var i = 0; i < boxController.data.sites.length; i++) {
            tr = trTemplate.clone();
            for (n in boxController.data.sites[i]) {
                var value = boxController.data.sites[i][n];
                tr.find('td.col-' + n).text(value);
                if (n != 'site') {
                    var currentTotalValue = 0;
                    if (n == 'average') {
                        currentTotalValue = parseFloat(trFooter.find('th.col-' + n).text());
                    } else {
                        currentTotalValue = parseInt(trFooter.find('th.col-' + n).text());
                    }
                    trFooter.find('th.col-' + n).text(value + currentTotalValue);
                }
            }
            table.find('tbody').append(tr);
        }
        trFooter.find('th.col-average').text(
            parseFloat(trFooter.find('th.col-average').text()) / 
            boxController.data.sites.length
        );
        boxController.afterLoadData();
    },
    
    construct: function () {}
    
});

var BC_RecentReviews = BoxController.extend({

    /**
     * @var String DOM id of the container div 
     */
    boxId: 'box-recent-reviews',
    
    endpoint: 'reviews',
    
    getReviewDetailsTemplate: function () {
        var template = $('<div>');
        
        
        return template;
    },
    
    beforeLoadData: function () {
        this.getContentDom().children().hide();
        this.getContentDom().append(this.getLoaderHtml());
        this.getHeaderDom().find('#box-header-status-filters').html($(this.getLoaderHtml()).children());
        this.getHeaderDom().find('#box-header-source-filters').html($(this.getLoaderHtml()).children());
    },
    
    loadHeaderFilters: function (filterType) {
        if (filterType != 'status' && filterType != 'source') {
            return;
        }
        var filters = this.data.filters[filterType];
        var filterHolder = this.getHeaderDom().find('#box-header-' + filterType + '-filters');
        filterHolder.html('');
        for (var i = 0; i < filters.length; i++) {
            var filterLink = $('<a href="#"></a>');
            if (filters[i].total) {
                filterLink.text(filters[i].total +' ');
            }
            filterLink.text(filterLink.text() + filters[i].value);
            filterHolder.append(filterLink);
            filterHolder.append(' ');
        }
    },
    
    loadReviews: function () {
        var table = this.getContentDom().find('.data-grid-holder table.data-grid');
        var trTemplate = table.find('tbody tr').clone();
        var trContentTemplate = '<tr><td colspan="6"></td></tr>';
        var tr = null;
        var trContent = null;
        table.find('tbody tr').remove();
        for (var i = 0; i < this.data.reviews.length; i++) {
            tr = trTemplate.clone();
            
            for (n in this.data.reviews[i]) {
                var value = this.data.reviews[i][n];
                if (n == 'submitted') {
                    var tmpDate = new Date(value * 1000);
                    tr.find('td.col-' + n).text(
                        monthNames[tmpDate.getMonth()] +
                        ' ' +
                        tmpDate.getDate()
                    );
                } else if (n == 'title') {
                    var titleLink = $('<a href="#"></a>');
                    titleLink.text(value);
                    titleLink.click(function () {
                        $(this).parents('tr:first').next().toggle('slow');
                        return false;
                    });
                    tr.find('td.col-' + n).html(titleLink);
                } else {
                    tr.find('td.col-' + n).text(value);
                }
            }
            
            if (i % 2) {
                tr.addClass('even');
            } else {
                tr.addClass('odd');
            }
            
            var checkbox = $('<input type="checkbox" name="id[]" value=""  />');
            checkbox.attr('value', this.data.reviews[i].id);
            tr.find('td.col-checkbox').html(checkbox);
            
            table.find('tbody').append(tr);
            
            trContent = $(trContentTemplate);
            trContent.css('display', 'none').find('td').text('adsfsf fsadfsa');
            table.find('tbody').append(trContent);
        }
        this.getContentDom().find('.ajax-loader').remove();
        this.getContentDom().find('.data-grid-holder').show();
    },
    
    loadDataCallback: function (data, textStatus, jqXHR) {
        var boxController = this.success.boxController;
        boxController.data = data;
        
        if (data.reviews) {
            boxController.loadReviews();
        }
        
        if (data.filters && data.filters.status) {
            boxController.loadHeaderFilters('status');
        }
        
        if (data.filters && data.filters.source) {
            boxController.loadHeaderFilters('source');
        }
    },
    
    construct: function () {}
    
});

var BC_SocialActivity = GraphBoxController.extend({

    /**
     * @var String DOM id of the container div 
     */
    boxId: 'box-social-activity',
    
    /**
     * @var String Name of the requested resource, used in Ajax URL
     */
    endpoint: 'social/activity',
    
    
    prepareGraph: function () {
        if (!this.data) {
            return;
        }
        return;
        var graphHolder = this.getGraphHolder();
        
        var graphHolderId = graphHolder.attr('id');
        
        var options = {
            chart: {
                renderTo: graphHolderId,
                margin: [10, 10, 10, 10],
                animation: false,
                defaultSeriesType: 'pie'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                       enabled: false
                    },
                    showInLegend: true
                }
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
                }
            },
            legend: {
                borderRadius: 0
            },
            series: [{
                 type: 'pie',
                 name: 'Browser share',
                 data: new Array()
             }]
        };
        for (var i = 0; i < this.data.networks.length; i++) {
            options.series[0].data.push(new Array(
                this.data.networks[i].keyword,
                this.data.networks[i].percent
            ));
        }
        
        this.graph = new Highcharts.Chart(options);
        
    },
    
    loadDataCallback: function (data, textStatus, jqXHR) {
        var boxController = this.success.boxController;
        boxController.data = data;
        var table = boxController.getContentDom().find('.data-grid-holder > table');
        var trTemplate = table.find('tbody tr').clone();
        var tr = null;
        table.find('tbody tr').remove();
        for (var i = 0; i < boxController.data.networks.length; i++) {
            tr = trTemplate.clone();
            for (n in boxController.data.networks[i]) {
                var value = boxController.data.networks[i][n];
                if (n == 'change') {
                    value = value + '%';
                }
                tr.find('td.col-' + n).text(value);
            }
            
            if (i % 2) {
                tr.addClass('even');
            } else {
                tr.addClass('odd');
            }
            table.find('tbody').append(tr);
        }
        boxController.afterLoadData();
    },
    
    construct: function () {}
    
});

var BC_SocialReach = BoxController.extend({

    /**
     * @var String DOM id of the container div 
     */
    boxId: 'box-social-reach',
    
    /**
     * @var String Name of the requested resource, used in Ajax URL
     */
    endpoint: 'social/reach',
    
    prepareGraph: function () {
        if (!this.data) {
            return;
        }
        return;
        var graphHolder = this.getGraphHolder();
        
        var graphHolderId = graphHolder.attr('id');
        
        var options = {
            chart: {
                renderTo: graphHolderId,
                margin: [10, 10, 10, 10],
                animation: false,
                defaultSeriesType: 'pie'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                       enabled: false
                    },
                    showInLegend: true
                }
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.y +' %';
                }
            },
            legend: {
                borderRadius: 0
            },
            series: [{
                 type: 'pie',
                 name: 'Browser share',
                 data: new Array()
             }]
        };
        for (var i = 0; i < this.data.networks.length; i++) {
            options.series[0].data.push(new Array(
                this.data.networks[i].keyword,
                this.data.networks[i].percent
            ));
        }
        
        this.graph = new Highcharts.Chart(options);
        
    },
    
    loadDataCallback: function (data, textStatus, jqXHR) {
        var boxController = this.success.boxController;
        boxController.data = data;
        var table = boxController.getContentDom().find('.data-grid-holder > table');
        var trTemplate = table.find('tbody tr').clone();
        var tr = null;
        table.find('tbody tr').remove();
        for (var i = 0; i < boxController.data.networks.length; i++) {
            tr = trTemplate.clone();
            for (n in boxController.data.networks[i]) {
                var value = boxController.data.networks[i][n];
                if (n == 'change') {
                    value = value + '%';
                }
                tr.find('td.col-' + n).text(value);
            }
            
            if (i % 2) {
                tr.addClass('even');
            } else {
                tr.addClass('odd');
            }
            table.find('tbody').append(tr);
        }
        boxController.afterLoadData();
    },
    
    construct: function () {}
    
});

var BC_SocialActivityDetails = BoxController.extend({

    /**
     * @var String DOM id of the container div 
     */
    boxId: 'box-social-activity-details',

    /**
     * @var String Name of the requested resource, used in Ajax URL
     */
    endpoint: 'socials',
    
    beforeLoadData: function () {
        this.getContentDom().children().hide();
        this.getContentDom().append(this.getLoaderHtml());
        this.getHeaderDom().find('#box-header-activity-filters').html($(this.getLoaderHtml()).children());
        this.getHeaderDom().find('#box-header-network-filters').html($(this.getLoaderHtml()).children());
    },
    
    loadHeaderFilters: function (filterType) {
        if (filterType != 'activity' && filterType != 'network') {
            return;
        }
        var filters = this.data.filters[filterType];
        var filterHolder = this.getHeaderDom().find('#box-header-' + filterType + '-filters');
        filterHolder.html('');
        for (var i = 0; i < filters.length; i++) {
            var filterLink = $('<a href="#"></a>');
            if (filters[i].total) {
                filterLink.text(filters[i].total +' ');
            }
            filterLink.text(filterLink.text() + filters[i].value);
            filterHolder.append(filterLink);
            filterHolder.append(' ');
        }
    },
    
    loadSocials: function () {
        var table = this.getContentDom().find('.data-grid-holder table.data-grid');
        var trTemplate = table.find('tbody tr').clone();
        var trContentTemplate = '<tr><td colspan="6"></td></tr>';
        var tr = null;
        var trContent = null;
        table.find('tbody tr').remove();
        for (var i = 0; i < this.data.socials.length; i++) {
            tr = trTemplate.clone();
            
            for (n in this.data.socials[i]) {
                var value = this.data.socials[i][n];
                if (n == 'submitted') {
                    var tmpDate = new Date(value * 1000);
                    tr.find('td.col-' + n).text(
                        monthNames[tmpDate.getMonth()] +
                        ' ' +
                        tmpDate.getDate()
                    );
                } else if (n == 'title') {
                    var titleLink = $('<a href="#"></a>');
                    titleLink.text(value);
                    titleLink.click(function () {
                        $(this).parents('tr:first').next().toggle('slow');
                        return false;
                    });
                    tr.find('td.col-' + n).html(titleLink);
                } else {
                    tr.find('td.col-' + n).text(value);
                }
            }
            
            if (i % 2) {
                tr.addClass('even');
            } else {
                tr.addClass('odd');
            }
            
            var checkbox = $('<input type="checkbox" name="id[]" value=""  />');
            checkbox.attr('value', this.data.socials[i].id);
            tr.find('td.col-checkbox').html(checkbox);
            
            table.find('tbody').append(tr);
            
            trContent = $(trContentTemplate);
            trContent.css('display', 'none').find('td').text('adsfsf fsadfsa');
            table.find('tbody').append(trContent);
        }
        this.getContentDom().find('.ajax-loader').remove();
        this.getContentDom().find('.data-grid-holder').show();
    },
    
    loadDataCallback: function (data, textStatus, jqXHR) {
        var boxController = this.success.boxController;
        boxController.data = data;
        
        if (data.socials) {
            boxController.loadSocials();
        }
        
        if (data.filters && data.filters.activity) {
            boxController.loadHeaderFilters('activity');
        }
        
        if (data.filters && data.filters.network) {
            boxController.loadHeaderFilters('network');
        }
    },
    
    construct: function () {}
    
});

boxManager = {
    
    collection: {},
    
    add: function (box) {
        if (!(box instanceof BoxController)) {
            return;
        }
        if (!box.getBoxId()) {
            return;
        }
        if (!box.endpoint) {
            return;
        }
        
        this.collection[box.getBoxId()] = box;
    },
    
    getBox: function (id) {
        if (this.collection[id]) {
            return this.collection[id];
        }
        return false;
    },
    
    moveEmptyToBottom: function () {
        
        var boxesHolder = $('#boxes-holder');
        var boxes = $('#boxes-holder .box-container');
        
        boxes.each(function (index) {
            var box = $(this);
            if (!box.children().length) {
                if (box.hasClass('box-container-left')
                    && !box.next().hasClass('active')) {
                        var tmp = box.next();
                        tmp.next('.clear').remove();
                        boxesHolder.append(box);
                        boxesHolder.append(tmp);
                        boxesHolder.append('<div class="clear"></div>');
                } else if (!box.hasClass('box-container-left')
                    && !box.hasClass('box-container-right')) {
                    box.nextAll(':last').after(box);
                }
            }
        });
    },
    
    initBoxes: function () {
        for (i in this.collection) {
            this.collection[i].init();
        }
    },
    
    init: function () {
        this.initBoxes();
        $( ".box" ).draggable({ 
            snap: ".box-container", 
            snapMode: 'inner',
            handle: ".box-header-button-move",
            revert: 'invalid',
            appendTo: 'body',
            zIndex: 10,
            start: function(event, ui) {
                $(this).css({});
                $('.box-container.empty').addClass('box-dropable')
                    .css('min-height', $(this).height());
            },
            stop: function (event, ui) {
                $(this).css({
                        top: 0,
                        left: 0,
                        width: 'auto'
                        });
                $('.box-container').css('min-height', '');
                $('.box-container.empty').removeClass('box-dropable');
            }
        });
        $('.box-container')
            .droppable({
                accept: '.box',
                activeClass: "box-dropable",
                hoverClass: "box-drag-over",
                drop: function (event, ui) {
                    var oldBox = $(this);
                    var fromContainer = ui.draggable.parent();
                    if (oldBox.children().length > 0) {
                        ui.draggable.parent().append(oldBox.children());
                    } else {
                        ui.draggable.parent().addClass('empty').removeClass('active');
                    }
                    $(this).removeClass('empty').addClass('active');
                    $(this).append(ui.draggable);
                    
                    boxManager.moveEmptyToBottom();
                    $(window).resize();
                }
            });
    }
};

boxManager.add(new BC_KeywordsAnalysis());
boxManager.add(new BC_ReviewSites());
boxManager.add(new BC_RecentReviews());
boxManager.add(new BC_SocialActivity());
boxManager.add(new BC_SocialReach());
boxManager.add(new BC_SocialActivityDetails());

$(document).ready(function () {
    boxManager.init();
});


var monthNames = [
    'Jan',
    'Feb',
    'March',
    'April',
    'May',
    'June',
    'July',
    'Aug',
    'Sept',
    'Oct',
    'Nov',
    'Dec'
    ];


