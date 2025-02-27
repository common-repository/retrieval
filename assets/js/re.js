"use strict";
! function() {
    jQuery(function(g) {
        var e, m = null != (e = window.wc_local_pickup_plus_frontend) ? e : {},
            t = function() {
                return g(".wc-lpp-help-tip").each(function() {
                    return g(this).tipTip({
                        content: g(this).data("tip"),
                        keepAlive: !0,
                        edgeOffset: 2
                    })
                })
            },
            a = function(e) {
                return !!e && (e.is(".processing") || e.parents(".processing").length)
            },
            r = function(e) {
                if (e && !a(e)) return e.addClass("processing").block({
                    message: null,
                    overlayCSS: {
                        background: "#fff",
                        opacity: .6
                    }
                })
            },
            l = function(e) {
                if (e) return e.removeClass("processing").unblock()
            },
            i = function() {
                var a = g("form.woocommerce-cart-form, div.cart_totals"),
                    i = g("#order_review");
                return g('input[name^="shipping_method"][type="radio"]').off("change.local-pickup-plus").on("change.local-pickup-plus", function() {
                    var e, t;
                    return r(a), r(i), e = g(this).is(":checked") && "local_pickup_plus" === g(this).val() ? "pickup" : "ship", t = g(this).attr("data-index"), e = {
                        action: "wc_local_pickup_plus_set_package_items_handling",
                        security: m.set_package_items_handling_nonce,
                        package_id: t,
                        handling: e
                    }, g.post({
                        url: m.ajax_url,
                        data: e
                    }).done(function(e) {
                        return e && e.success ? m.is_cart ? g(document.body).on("updated_shipping_method", function() {
                            return g(document).trigger("wc_update_cart")
                        }) : m.is_checkout ? (m.apply_pickup_location_tax && g(document.body).one("updated_checkout", function() {
                            return g(document.body).trigger("update_checkout")
                        }), g(document.body).trigger("update_checkout")) : void 0 : console.log(e)
                    }).always(function() {
                        return l(a), l(i)
                    })
                })
            },
            n = function(e) {
                var t, a;
                return e.id ? (a = t = "", e.name && e.address ? (a = e.name, t = e.address) : g(e.element) && (a = g(e.element).data("name"), t = g(e.element).data("address")), "" !== a ? g('<div id="wc-local-pickup-plus-pickup-location-option-' + e.id + '" class="wc-local-pickup-plus-pickup-location-option"><span class="wc-location-pickup-plus-pickup-location-option-name" style="display:block;">' + a + '</span><small class="wc-location-pickup-plus-pickup-location-option-address" style="display:inline-block;">' + t + "</small></div>") : e.text) : e.text || ""
            },
            c = function() {
                var e;
                return g("a.enable-local-pickup, a.disable-local-pickup").on("click", function(e) {
                    var t, a, i, n, c, o;
                    return e.preventDefault(), i = g(this).parent().parent(), t = g("form.woocommerce-cart-form, div.cart_totals"), a = g("#order_review"), g(this).hasClass("enable-local-pickup") ? (o = "pickup", i.find("a.enable-local-pickup").parent().hide(), i.find("a.disable-local-pickup").parent().show(), i.find("> div").show()) : (o = "ship", i.find("a.enable-local-pickup").parent().show(), i.find("a.disable-local-pickup").parent().hide(), i.find("> div").hide()), r(t), r(a), n = i.find(".pickup-location-lookup-area"), (e = (e = (c = i.find(".pickup-location-lookup")).attr("data-single-pickup-location")) && 0 < parseInt(e, 10) ? e : c.val()) || "per-order" !== m.pickup_selection_mode || (e = (c = g('[name="shipping_method_pickup_location_id[0]"]')).length ? c.val() : e), e = {
                        action: "wc_local_pickup_plus_set_cart_item_handling",
                        security: m.set_cart_item_handling_nonce,
                        cart_item_key: i.data("pickup-object-id"),
                        pickup_data: {
                            handling: o,
                            lookup_area: n.val(),
                            pickup_location_id: e
                        }
                    }, g.post(m.ajax_url, e, function(e) {
                        return "per-order" === m.pickup_selection_mode && g(".pickup-location-lookup").trigger("change"), l(t), l(a), e.success ? m.is_cart ? g(document).trigger("wc_update_cart") : m.is_checkout ? g(document.body).trigger("update_checkout") : void 0 : console.log(e)
                    })
                }), g(".pickup-location-change-lookup-area").on("click", function(e) {
                    return e.preventDefault(), g(this).closest("div.pickup-location-lookup-area-field").find("> div").toggle()
                }), (e = g("select.pickup-location-lookup-area")).select2(), e.on("change", function() {
                    var t, a, e, i, n, c = g(this).closest(".pickup-location-lookup-area-field").data("pickup-object-id"),
                        o = g("#pickup-location-field-for-" + c).find("a.enable-local-pickup");
                    if (!o.is(":visible") && (i = g("#pickup-location-lookup-area-field-for-" + c).find("em.pickup-location-current-lookup-area-label"), n = g(this).select2("data"), t = g("form.woocommerce-cart-form, div.cart_totals"), a = g("#order_review"), n && n[0] && n[0].text && i.text(n[0].text), r(t), r(a), i = (o = g("#pickup-location-field-for-" + c).find(".pickup-location-lookup")).data("pickup-object-type"), o = (n = o.attr("data-single-pickup-location")) && 0 < parseInt(n, 10) ? n : o.val(), "cart-item" === i ? e = {
                        action: "wc_local_pickup_plus_set_cart_item_handling",
                        security: m.set_cart_item_handling_nonce,
                        cart_item_key: c,
                        pickup_data: {
                            handling: "pickup",
                            lookup_area: g(this).val(),
                            pickup_location_id: o
                        }
                    } : "package" === i && (i = g("#wc-local-pickup-plus-datepicker-" + c), e = {
                        action: "wc_local_pickup_plus_set_package_handling",
                        security: m.set_package_handling_nonce,
                        package_id: c,
                        lookup_area: g(this).val(),
                        pickup_location_id: o
                    }, i.length && i.val() && (e.pickup_date = i.val())), e)) return g.post(m.ajax_url, e, function(e) {
                        return l(t), l(a), e.success ? m.is_cart ? g(document).trigger("wc_update_cart") : m.is_checkout ? g(document.body).trigger("update_checkout") : void 0 : console.log(e)
                    })
                })
            },
            o = function() {
                var t = function(e, t, a) {
                        var i, n;
                        return e = e ? e.toLowerCase() : "", t = t ? t.toLowerCase() : "", i = !1, e && !(i = t && -1 < t.indexOf(e) ? !0 : i) && a && (n = String(g(a.element).data("postcode")), t = g(a.element).data("city"), a = g(a.element).data("address"), !(i = !(i = n && -1 < n.toString().toLowerCase().indexOf(e) ? !0 : i) && t && -1 < t.toString().toLowerCase().indexOf(e) ? !0 : i) && a && -1 < a.toString().toLowerCase().indexOf(e) && (i = !0)), i
                    },
                    i = g("select.pickup-location-lookup"),
                    a = i.closest("td").css("width");
                return m.use_enhanced_search ? i.select2({
                    initSelection: function(e, t) {
                        var a = e.val();
                        return "" !== a ? (e = {
                            action: "wc_local_pickup_plus_get_pickup_location_name",
                            security: m.get_pickup_location_name_nonce,
                            id: a
                        }, g.post(m.ajax_url, e, function(e) {
                            if (e && e.success && e.data) return t({
                                id: a,
                                text: e.data
                            })
                        })) : t({
                            text: i.data("placeholder")
                        })
                    },
                    minimumInputLength: 2,
                    language: {
                        inputTooShort: function() {
                            return m.i18n.search_type_minimum_characters
                        },
                        errorLoading: function() {
                            return m.i18n.search_error_loading
                        },
                        loadingMore: function() {
                            return m.i18n.search_loading_more
                        },
                        noResults: function() {
                            return m.i18n.search_no_results
                        },
                        searching: function() {
                            return m.i18n.search_searching
                        }
                    },
                    templateResult: n,
                    ajax: {
                        url: m.ajax_url,
                        cache: !1,
                        dataType: "json",
                        delay: 250,
                        data: function(e) {
                            var t = g(this).data("product-id"),
                                a = g(this).data("pickup-object-id"),
                                i = g(this).parent().find("#pickup-location-lookup-area-for-" + a);
                            return {
                                term: e.term,
                                area: i ? i.find("option:selected").val() : "",
                                cart_item_id: a,
                                product_id: t,
                                page: e.page,
                                security: m.pickup_locations_lookup_nonce,
                                action: "wc_local_pickup_plus_pickup_locations_lookup"
                            }
                        },
                        processResults: function(e, t) {
                            e = e && e.success && e.data ? e.data : [];
                            return {
                                results: e
                            }
                        }
                    },
                    width: a
                }) : g.fn.select2.amd.require(["select2/compat/matcher"], function(e) {
                    return i.select2({
                        templateResult: n,
                        matcher: e(t),
                        width: a
                    })
                }), i.off("change.local-pickup-plus").on("change.local-pickup-plus", function(e) {
                    var t, a, i, n, c, o = g("form.woocommerce-cart-form, div.cart_totals"),
                        p = g("#order_review");
                    if (r(o), r(p), n = g(this).data("pickup-object-type"), c = g(this).data("pickup-object-id"), a = g(this).val(), i = g("#pickup-location-lookup-area-for-" + c).val(), "cart-item" === n ? t = {
                        action: "wc_local_pickup_plus_set_cart_item_handling",
                        security: m.set_cart_item_handling_nonce,
                        cart_item_key: c,
                        pickup_data: {
                            handling: "pickup",
                            lookup_area: i,
                            pickup_location_id: a
                        }
                    } : "package" === n && (t = {
                        action: "wc_local_pickup_plus_set_package_handling",
                        security: m.set_package_handling_nonce,
                        package_id: c,
                        lookup_area: i,
                        pickup_location_id: a
                    }), t) return g.post(m.ajax_url, t, function(e) {
                        return l(o), l(p), e.success ? m.is_cart ? g(document).trigger("wc_update_cart") : m.is_checkout ? g(document.body).trigger("update_checkout") : void 0 : console.log(e)
                    })
                })
            },
            p = function() {
                var e, t;
                if (!m.display_shipping_address_fields) return (t = "per-item" === m.pickup_selection_mode ? (e = parseInt(g("#wc-local-pickup-plus-packages-to-pickup").val(), 10), parseInt(g("#wc-local-pickup-plus-packages-to-ship").val(), 10)) : (e = (t = g(".woocommerce-shipping-methods")).find("input[value=local_pickup_plus]:checked").length, t.find("input[type=radio]:checked").not("input[value=local_pickup_plus]").length)) < e && 0 === t ? (g("#shiptobilling, #ship-to-different-address").hide(), g("#shiptobilling, #ship-to-different-address").parent().find("h3").hide(), g("#ship-to-different-address input").prop("checked", !1), g(".shipping_address").hide()) : (g("#shiptobilling, #ship-to-different-address").show(), g("#shiptobilling, #ship-to-different-address").parent().find("h3").show(), g("#ship-to-different-address input").is(":checked") ? g(".shipping_address").show() : g(".shipping_address").hide())
            },
            u = function() {
                return g(".woocommerce-shipping-totals").each(function() {
                    if ("local_pickup_plus" === g(this).find("input.shipping_method:checked").val() || "local_pickup_plus" === g(this).find("input:hidden.shipping_method").val()) return g(this).find("p.woocommerce-shipping-destination").hide(), g(this).find(".woocommerce-shipping-calculator").hide()
                })
            };
        return t(), i(), c(), o(), "undefined" != typeof wc_cart_params && u(), "undefined" != typeof wc_checkout_params && p(), g(document.body).on("updated_checkout", function() {
            var a = function(t, e) {
                    var i, n, c, a, o, p = 2 < arguments.length && void 0 !== arguments[2] && arguments[2],
                        r = t.find("input.pickup-location-appointment-date"),
                        l = t.find("input.pickup-location-appointment-date-alt"),
                        u = t.find("[id^=wc-local-pickup-plus-date-clear-]"),
                        d = r.val(),
                        s = r.data("location-id"),
                        _ = r.data("package-id"),
                        k = "" !== d ? new Date(d) : e.default_date && "" !== e.default_date ? new Date(1e3 * e.default_date) : null,
                        f = e.unavailable_dates ? g.map(e.unavailable_dates, function(e) {
                            return e
                        }) : [];
                    return p && (r.attr("value", ""), r.trigger("change"), r.removeClass("hasDatepicker"), r.datepicker("destroy")), d = new Date(1e3 * e.calendar_start), p = new Date(1e3 * e.calendar_end), d = new Date(d.getTime() + 60 * d.getTimezoneOffset() * 1e3), p = new Date(p.getTime() + 60 * p.getTimezoneOffset() * 1e3), k = new Date(k.getTime() + 60 * k.getTimezoneOffset() * 1e3), r.datepicker({
                        minDate: d,
                        maxDate: p,
                        altField: "#" + l.attr("id"),
                        altFormat: "yy-mm-dd",
                        dateFormat: m.date_format,
                        defaultDate: k || null,
                        firstDay: m.start_of_week,
                        monthNames: m.month_names,
                        dayNamesMin: m.day_initials,
                        prevText: "",
                        nextText: "",
                        showOn: "both",
                        buttonText: "&nbsp;",
                        gotoCurrent: !0,
                        beforeShow: function(e, t) {
                            return g(t.dpDiv).addClass("pickup-location-appointment-datepicker").addClass("pickup-location-appointment-datepicker-" + _)
                        },
                        beforeShowDay: function(e) {
                            e = g.datepicker.formatDate("yy-mm-dd", e);
                            return [-1 === f.indexOf(e)]
                        },
                        onSelect: h
                    }).one("init", function(e) {
                        return g("button.ui-datepicker-trigger").attr("title", m.i18n.datepicker_button)
                    }).trigger("init"), i = null, n = c = o = 0, r.on("keydown", function(e) {
                        var t, a;
                        if (i && null !== i && 0 !== i.length || ((t = (a = g(".pickup-location-appointment-datepicker-" + _).find(".ui-datepicker-calendar")).find(".ui-datepicker-current-day")) && 0 !== t.length ? i = t : (t = a.find("tbody tr td").not(".ui-datepicker-unselectable, .ui-state-disable").first(), (i = t) && !i.hasClass("ui-datepicker-cursor") && i.addClass("ui-datepicker-cursor"))), "ArrowLeft" === e.key ? t = i.prevAll("td").not(".ui-datepicker-unselectable, .ui-state-disable").first() : "ArrowRight" === e.key ? t = i.nextAll("td").not(".ui-datepicker-unselectable, .ui-state-disable").first() : "ArrowUp" === e.key ? t = (t = i.parent("tr").prevAll("tr").first().find("td")) ? t.not(".ui-datepicker-unselectable, .ui-state-disable").first() : null : "ArrowDown" === e.key ? t = (t = i.parent("tr").nextAll("tr").first().find("td")) ? t.not(".ui-datepicker-unselectable, .ui-state-disable").first() : null : "PageUp" === e.key || "PageDown" === e.key ? (a = g(".pickup-location-appointment-datepicker-" + _).find(".ui-datepicker-calendar"), t = "PageDown" === e.key ? a ? a.find("tbody tr td").not(".ui-datepicker-unselectable, .ui-state-disable").first() : null : a ? a.find("tbody tr td").not(".ui-datepicker-unselectable, .ui-state-disable").last() : null) : "Enter" === e.key && 0 < o && 0 < c && 0 < n && i.trigger("click"), t && "selectDay" === t.data("handler") && (e.preventDefault(), i.removeClass("ui-datepicker-cursor"), (i = t.first()).addClass("ui-datepicker-cursor"), o = parseInt(i.data("year"), 10), c = parseInt(i.data("month"), 10), n = parseInt(i.find("a").text(), 10), 0 < o && 0 < c && 0 < n)) return g("#wc-local-pickup-plus-datepicker-" + _ + "-live-region").html(g.datepicker.formatDate(m.date_format, new Date(o, c, n)))
                    }), (k = t.find("select.pickup-location-appointment-offset")).select2(), k.on("change", function(e) {
                        return e.preventDefault(), e.stopPropagation(), h(g.datepicker.formatDate("yy-mm-dd", r.datepicker("getDate")), r)
                    }), (k = (k = r.val()) && k.match(/^\d{4}-\d{2}-\d{2}$/) ? g.datepicker.parseDate("yy-mm-dd", k) : null) ? (r.datepicker("setDate", k), g("#ui-datepicker-div").hide()) : e.auto_select_default && e.default_date && "" !== e.default_date && (a = g.datepicker.parseDate("yy-mm-dd", e.default_date)) && (r.datepicker("setDate", a), g("#ui-datepicker-div").hide(), h(a, r)), u.on("click", function(e) {
                        return e.preventDefault(), r.datepicker("setDate", null), r.attr("value", ""), e = {
                            action: "wc_local_pickup_plus_set_package_handling",
                            security: m.set_package_handling_nonce,
                            pickup_date: "",
                            package_id: _,
                            pickup_location_id: s
                        }, g.post(m.ajax_url, e, function(e) {
                            return e.success || console.log(e), t.find(".pickup-location-schedule").empty(), g(document.body).trigger("update_checkout")
                        })
                    })
                },
                h = function(e, t) {
                    var a, i = t.input || t,
                        n = i.parent().parent(),
                        c = i.data("location-id"),
                        o = i.data("package-id"),
                        p = g("#wc-local-pickup-plus-pickup-date-" + o).val(),
                        r = g("#wc-local-pickup-plus-pickup-appointment-offset-" + o),
                        t = r.val();
                    if (e && p && new Date(p)) return i.attr("value", e), a = {
                        action: "wc_local_pickup_plus_set_package_handling",
                        security: m.set_package_handling_nonce,
                        pickup_date: p,
                        package_id: o,
                        pickup_location_id: c,
                        appointment_offset: t
                    }, g.post(m.ajax_url, a, function(e) {
                        return e.success || console.log(e), 0 === r.length ? (a = {
                            action: "wc_local_pickup_plus_get_pickup_location_opening_hours_list",
                            security: m.get_pickup_location_opening_hours_list_nonce,
                            location: c,
                            package_id: o,
                            date: p
                        }, g.post(m.ajax_url, a, function(e) {
                            var t = n.find(".pickup-location-schedule");
                            if (t.empty(), e && e.success) return t.append(e.data), g(document.body).trigger("update_checkout")
                        })) : g(document.body).trigger("update_checkout")
                    })
                },
                e = g(".pickup-location-appointment");
            return e && e.each(function() {
                var t = g(this),
                    e = t.find("input.pickup-location-appointment-date").data("location-id"),
                    e = {
                        action: "wc_local_pickup_plus_get_pickup_location_appointment_data",
                        security: m.get_pickup_location_appointment_data_nonce,
                        location: e
                    };
                return g.post(m.ajax_url, e, function(e) {
                    if (e.success) return a(t, e.data)
                })
            }), t(), i(), c(), o(), p(), g("#order_review").find("> p.woocommerce-shipping-contents").remove()
        }), g("#order_review").find("> p.woocommerce-shipping-contents").remove(), g(document.body).on("updated_cart_totals", function() {
            return t(), c(), o(), u()
        })
    })
}.call(void 0);