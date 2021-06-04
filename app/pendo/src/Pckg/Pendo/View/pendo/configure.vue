<template>
    <div style="text-align: center; padding: 2rem; display: block; max-width: 360px; width: 100%; margin: 0 auto;">
        <div v-if="loading">
            Loading ...
        </div>
        <div v-else-if="!certificate || !certificate.invisiblePassword">
            <p>Upload certificate password for company <b>{{ company.short_name }}</b>,
                VAT number <b>{{ company.vat_number }}</b>.</p>

            <pckg-htmlbuilder-dropzone :url="'/configure/' + apiKey + '/certificate'"
                                       id="certificate-upload"
                                       :params="{acceptedFiles: 'application/x-pkcs12'}"
                                       :preview="false"
                                       @uploaded="certificateUploaded"></pckg-htmlbuilder-dropzone>

            <template v-if="certificate">
                <div class="form-group">
                    <label>Certificate password</label>
                    <input type="password" class="form-control" v-model="certificate.password"/>
                </div>

                <div class="form-group">
                    <button type="button" @click.prevent="checkCertificate" class="btn btn-primary"
                            :disabled="!certificate || (certificate.password || '').length < 6">Validate and save
                    </button>
                </div>
            </template>

            <b v-if="errorCode" style="color: red;">{{ errorCode }}</b>

        </div>
        <div v-else-if="certificate.password">

            <p>Your certificate was successfully activated. Make sure to set correct invoice settings, activate payment
                methods and add permissions for cashiers.</p>

            <button type="button" @click.prevent="certificate.password = null" class="btn btn-primary">Finish</button>

        </div>
        <div v-else>
            <p>Country: <b>{{ company.country.code }}</b></p>
            <p>Company: <b>{{ company.short_name }}</b></p>
            <p>VAT number: <b>{{ company.vat_number }}</b></p>
            <p>Business number: <b>{{ company.business_number }}</b></p>
            <p>Contact: <b>{{ user.email }}</b></p>
            <!--<p>Comms store: <b>{{ appKey.app.id }}</b></p>-->
            <!--<p>Certificate: <b>{{ certificate.invisiblePassword }}</b></p>-->
            <hr/>
            <!--<button class="btn btn-info" @click.prevent="makeEchoTest">Make echo test</button>
            &nbsp;
            <button class="btn btn-info" @click.prevent="makeInvoiceTest">Make invoice test</button>-->
        </div>
    </div>
</template>

<script>
    const PckgHtmlbuilderDropzoneComponent = () => import ("../../../../../../../vendor/pckg/helpers-js/vue/pckg-htmlbuilder-dropzone.vue");
    export default {
        props: {
            apiKey: {
                required: true
            }
        },
        components: {
            'pckg-htmlbuilder-dropzone': PckgHtmlbuilderDropzoneComponent,
        },
        data: function () {
            return {
                state: null,
                loading: true,
                appKey: null,
                company: null,
                certificate: null,
                errorCode: null
            };
        },
        methods: {
            certificateUploaded: function (data) {
                if (!data.url) {
                    this.errorCode = (data.data ? data.data.message : null) || 'Something went wrong';
                    return;
                }
                this.errorCode = null;
                this.certificate = {
                    invisiblePassword: null,
                    password: null,
                    hash: data.url
                };
            },
            checkCertificate: function () {
                http.post('/configure/' + this.apiKey + '/validate-certificate', this.certificate, function (data) {
                    if (data.success) {
                        this.errorCode = null;
                        this.certificate.invisiblePassword = this.certificate.password[0] + '********' + this.certificate.password[this.certificate.password.length - 1];
                        return;
                    }

                    this.errorCode = data.status || 'STATUS_ERROR';
                }.bind(this), function () {
                    this.errorCode = data.status || 'OTHER_ERROR';
                });
            },
            initialFetch: function () {
                http.get('/api/app-key/' + this.apiKey, function (data) {
                    this.company = data.company;
                    this.user = data.user;
                    this.appKey = data.appKey;
                    this.certificate = data.certificate;
                    this.loading = false;
                }.bind(this), function () {
                    this.state = 'error';
                }.bind(this));
            },
        },
        created: function () {
            this.initialFetch();
        }
    }
</script>
