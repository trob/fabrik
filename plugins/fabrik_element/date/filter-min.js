var DateFilter=new Class({Implements:[Options],options:{calendarSetup:{eventName:"click",ifFormat:"%Y/%m/%d",daFormat:"%Y/%m/%d",singleClick:true,align:"Br",range:[1900,2999],showsTime:false,timeFormat:"24",electric:true,step:2,cache:false,showOthers:false}},initialize:function(b){this.setOptions(b);this.cals=$H({});for(var a=0;a<this.options.ids.length;a++){this.makeCalendar(this.options.ids[a],this.options.buttons[a])}},makeCalendar:function(g,c){if(this.cals[g]){this.cals[g].show();return}c=document.id(c);if(typeOf(c)==="null"){return}this.addEventToCalOpts();var f=Object.clone(this.options.calendarSetup);var b=["displayArea","button"];f.inputField=document.id(g);var a=f.inputField||f.displayArea;var e=f.inputField?f.ifFormat:f.daFormat;this.cals[g]=null;if(a){f.date=Date.parseDate(a.value||a.innerHTML,e)}this.cals[g]=new Calendar(f.firstDay,f.date,f.onSelect,f.onClose);this.cals[g].setDateStatusHandler(f.dateStatusFunc);this.cals[g].setDateToolTipHandler(f.dateTooltipFunc);this.cals[g].showsTime=f.showsTime;this.cals[g].time24=(f.timeFormat.toString()==="24");this.cals[g].weekNumbers=f.weekNumbers;this.cals[g].showsOtherMonths=f.showOthers;this.cals[g].yearStep=f.step;this.cals[g].setRange(f.range[0],f.range[1]);this.cals[g].params=f;this.cals[g].params.button=c;this.cals[g].getDateText=f.dateText;this.cals[g].setDateFormat(e);this.cals[g].create();this.cals[g].refresh();this.cals[g].hide();c.addEvent("click",function(h){if(!this.cals[g].params.position){this.cals[g].showAtElement(this.cals[g].params.button||this.cals[g].params.displayArea||this.cals[g].params.inputField,this.cals[g].params.align)}else{this.cal.showAt(this.cals[g].params.position[0],paramss[g].position[1])}this.cals[g].show()}.bind(this));this.cals[g].params.inputField.addEvent("blur",function(i){var h=this.cals[g].params.inputField.value;if(h!==""){var j=Date.parseDate(h,this.cals[g].params.ifFormat);this.cals[g].date=j}}.bind(this));var d=function(){this.cals[g].hide()};d.delay(100,this);return this.cals[g]},dateSelect:function(a){return false},calSelect:function(b,a){this.update(b,b.date.format("db"));if(b.dateClicked){b.callCloseHandler()}},calClose:function(a){a.hide()},update:function(b,a){this.getElement();if(a){if(typeOf(a)==="string"){a=Date.parse(a)}b.params.inputField.value=a.format(this.options.calendarSetup.ifFormat)}},addEventToCalOpts:function(){this.options.calendarSetup.onSelect=function(b,a){this.calSelect(b,a)}.bind(this);this.options.calendarSetup.dateStatusFunc=function(a){return this.dateSelect(a)}.bind(this);this.options.calendarSetup.onClose=function(a){this.calClose(a)}.bind(this)},onSubmit:function(){this.cals.each(function(a){if(a.params.inputField.value!==""){a.params.inputField.value=a.date.format("db")}}.bind(this))},onUpdateData:function(){this.cals.each(function(a){if(a.params.inputField.value!==""){this.update(a,a.date)}}.bind(this))}});