<template>
    <div style="text-align: center; padding: 2rem; display: block; max-width: 360px; width: 100%; margin: 0 auto;">
        <div v-if="loading">
            Loading ...
        </div>
        <div v-else-if="!certificate || !certificate.invisiblePassword">
            <p>Upload certificate and enter certificates password for company <b>{{ company.short_name }}</b>,
                VAT number <b>{{ company.vat_number }}</b>.</p>

            <pckg-htmlbuilder-dropzone :url="'/configure/' + apiKey + '/certificate'"
                                       id="certificate-upload"
                                       :params="{acceptedFiles: 'application/x-pkcs12'}"
                                       @uploaded="certificateUploaded"></pckg-htmlbuilder-dropzone>

            <div class="form-group" v-if="certificate">
                <label>Certificate password</label>
                <input type="password" class="form-control" v-model="certificate.password"/>
            </div>

            <b v-if="errorCode">{{ errorCode }}</b>

            <button type="button" @click.prevent="checkCertificate" class="btn btn-primary"
                    :disabled="!certificate || (certificate.password || '').length < 6">Check validity
            </button>
        </div>
        <div v-else-if="certificate.password">

            <p>Success! You have successfully connected certificate. Now you can start fiscalizing your invoices.</p>

            <button type="button" @click.prevent="certificate.password = null" class="btn btn-primary">Continue</button>

        </div>
        <div v-else>
            <p>Country: <b>{{ company.country.code }}</b></p>
            <p>Company: <b>{{ company.short_name }}</b></p>
            <p>VAT number: <b>{{ company.vat_number }}</b></p>
            <p>Contact: <b>{{ user.email }}</b></p>
            <p>Comms store: <b>{{ appKey.app.id }}</b></p>
            <p>Certificate: <b>{{ certificate.invisiblePassword }}</b></p>
            <hr/>
            <button class="btn btn-info" @click.prevent="makeEchoTest">Make echo test</button>
            &nbsp;
            <button class="btn btn-info" @click.prevent="makeInvoiceTest">Make invoice test</button>
        </div>
    </div>
</template>

<script>
    const PckgHtmlbuilderDropzoneComponent = () => import ("../../../../../../../vendor/pckg/generic/src/Pckg/Maestro/public/vue/pckg-htmlbuilder-dropzone.vue");
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
                this.company = {
                    short_name: 'Bojan Rajh s.p.',
                    vat_number: '82766347',
                    country: {
                        code: 'SI'
                    }
                };
                this.user = {
                    email: 'bojan.rajh.sp@gmail.com'
                };
                this.appKey = {
                    key: 'hidden',
                    app: {
                        id: 'wsd58a5a'
                    }
                };
                /*this.certificate = {
                    invisiblePassword: 'a*********z'
                };*/
                this.loading = false;
                return;
                http.get('/api/api-key/' + this.apiKey, function (data) {
                    this.apiKeyObject = data.apiKey;
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