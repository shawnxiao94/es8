webpackJsonp(["app/js/testpaper-manage/picker/index"],[function(t,e,a){"use strict";function n(t){return t&&t.__esModule?t:{"default":t}}function i(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}var r=function(){function t(t,e){for(var a=0;a<e.length;a++){var n=e[a];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,n.key,n)}}return function(e,a,n){return a&&t(e.prototype,a),n&&t(e,n),e}}(),o=a("63fff8fb24f3bd1f61cd");n(o);a("b3c50df5d8bf6315aeba");var s=a("b334fd7e4c5a19234db2"),c=n(s),d=a("de585ca0d3c2d0205c51"),u=n(d),l=a("f637e828bcb096623369"),f=n(l),h=function(){function t(e,a,n){i(this,t),this.$button=e,this.$typeNav=a,this.$form=n,this.$modal=$("#testpaper-confirm-modal"),this.currentType=this.$typeNav.find(".active").children().data("type"),this._initEvent(),this._initSortList(),this.questions=[]}return r(t,[{key:"_init",value:function(){}},{key:"_initSortList",value:function(){this.$form.find("table").sortable({containerPath:"> tr",itemSelector:"tr.is-question",placeholder:'<tr class="placeholder"/>',exclude:".notMoveHandle",onDrop:function(t,e,a){a(t,e),t.hasClass("have-sub-questions")&&!function(){var e=t.parents("tbody");e.find("tr.is-question").each(function(){var t=$(this);e.find("[data-parent-id="+t.data("id")+"]").detach().insertAfter(t)})}(),self.refreshSeqs()}})}},{key:"_initEvent",value:function(){var t=this;this.$button.on("click",function(e){return t._showPickerModal(e)}),this.$typeNav.on("click","li",function(e){return t._changeNav(e)}),this.$form.on("click",'[data-role="item-delete-btn"]',function(e){return t._deleteItem(e)}),this.$form.on("click",'[data-role="replace-item"]',function(e){return t._replaceItem(e)}),this.$form.on("click",".request-save",function(e){return t._confirmSave(e)}),this.$modal.on("click",".confirm-submit",function(e){return t._submitSave(e)})}},{key:"_showPickerModal",value:function(t){var e=[];$('[data-type="'+this.currentType+'"]').find('[name="questionId[]"]').each(function(){e.push($(this).val())});var a=$("#modal").modal();a.data("manager",this),$.get(this.$button.data("url"),{excludeIds:e.join(","),type:this.currentType},function(t){a.html(t)})}},{key:"_changeNav",value:function(t){var e=$(t.currentTarget),a=e.children().data("type");this.currentType=a,this.$typeNav.find("li").removeClass("active"),e.addClass("active"),this.$form.find('[data-role="question-body"]').addClass("hide"),this.$form.find("#testpaper-items-"+a).removeClass("hide")}},{key:"_deleteItem",value:function(t){var e=$(t.currentTarget),a=e.closest("tr").data("id");e.closest("tbody").find('[data-parent-id="'+a+'"]').remove(),e.closest("tr").remove()}},{key:"_replaceItem",value:function(t){var e=$(t.currentTarget),a=[];$('[data-role="question-body"]:visible').find('[name="questionId[]"]').each(function(){a.push($(this).val())});var n=$("#modal").modal();n.data("manager",this),$.get(e.data("url"),{excludeIds:a.join(","),type:this.currentType},function(t){n.html(t)})}},{key:"_confirmSave",value:function(t){var e=this._validateScore();if(e){if($('[name="passedScore"]').length>0){var a=$(".passedScoreDiv").siblings(".help-block").html();if(""!=$.trim(a))return}var n=this._calTestpaperStats(),i="";$.each(n,function(t,e){var a="<tr>";a+="<td>"+e.name+"</td>",a+="<td>"+e.count+"</td>",a+="<td>"+e.score.toFixed(1)+"</td>",a+="</tr>",i+=a}),this.$modal.find(".detail-tbody").html(i),this.$modal.modal("show")}}},{key:"_validateScore",value:function(){var t=!0;return 0==this.$form.find('[name="scores[]"]').length&&((0,c.default)("danger","请选择题目。"),t=!1),this.$form.find('input[type="text"][name="scores[]"]').each(function(){var e=$(this).val();"0"==e&&((0,c.default)("danger","题目分值不能为0。"),t=!1),/^(([1-9]{1}\d{0,2})|([0]{1}))(\.(\d){1})?$/.test(e)||((0,c.default)("danger","题目分值只能填写数字，并且在3位数以内，保留一位小数。"),$(this).focus(),t=!1)}),t}},{key:"_calTestpaperStats",value:function(){var t={},e=this;this.$typeNav.find("li").each(function(){var a=$(this).find("a").data("type"),n=$(this).find("a").data("name");t[a]={name:n,count:0,score:0,missScore:0},e.$form.find("#testpaper-items-"+a).find('[name="scores[]"]').each(function(){var n=$(this).closest("tr").data("type"),i="material"==n?0:parseFloat($(this).val()),r={};"material"!=n&&t[a].count++,t[a].score+=i,t[a].missScore=parseFloat($(this).data("miss-score"));var o=$(this).closest("tr").data("id");r.id=o,r.score=i,r.missScore=parseFloat($(this).data("miss-score")),r.type=a,e.questions.push(r)})});var a={name:Translator.trans("总计"),count:0,score:0};return $.each(t,function(t,e){a.count+=e.count,a.score+=e.score}),t.total=a,t}},{key:"_submitSave",value:function(t){$.post(this.$form.attr("action"),{questions:this.questions},function(t){t.goto&&(window.location.href=t.goto)})}}]),t}(),m=$("#question-checked-form");new h($('[data-role="pick-item"]'),$(".nav-mini"),m),new u.default(m),new f.default(m)}]);