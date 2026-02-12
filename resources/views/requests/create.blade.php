@extends('layouts.app')

@section('title', 'New Access Request')

@section('sidebar')
    <a href="{{ route('dashboard.requester') }}" class="nav-link">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="{{ route('requests.create') }}" class="nav-link active">
        <i class="bi bi-plus-circle"></i> New Request
    </a>
    <a href="{{ route('requests.index') }}" class="nav-link">
        <i class="bi bi-list-ul"></i> My Requests
    </a>
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="page-title">New Access Request</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dashboard.requester') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">New Request</li>
            </ol>
        </nav>
    </div>

    <div class="card">
        <div class="card-header">
            <i class="bi bi-file-earmark-plus"></i> Request Form
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('requests.store') }}" id="requestForm" x-data="requestForm()">
                @csrf

                <!-- Step 1: Department and Template Selection -->
                <div class="step-section mb-4">
                    <h5 class="mb-3">
                        <span class="badge bg-primary">1</span> Select Department and Template
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="department_id" class="form-label">
                                Department <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('department_id') is-invalid @enderror" 
                                    id="department_id" 
                                    name="department_id"
                                    x-model="selectedDepartment"
                                    @change="loadTemplates()"
                                    required>
                                <option value="">-- Select Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="template_id" class="form-label">
                                Template <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('template_id') is-invalid @enderror" 
                                    id="template_id" 
                                    name="template_id"
                                    x-model="selectedTemplate"
                                    @change="loadTemplateDetails()"
                                    :disabled="!selectedDepartment"
                                    required>
                                <option value="">-- Select Template --</option>
                                <template x-for="template in templates" :key="template.id">
                                    <option :value="template.id" x-text="`${template.mnemonic} - ${template.name}`"></option>
                                </template>
                            </select>
                            @error('template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Select the template that matches the user's job role</small>
                        </div>
                    </div>

                    <!-- Template Details Preview -->
                    <div x-show="templateDetails" class="alert alert-info" x-cloak>
                        <h6><i class="bi bi-info-circle"></i> Template Information</h6>
                        <div><strong>Template:</strong> <span x-text="templateDetails?.display_name"></span></div>
                        <div><strong>EHR Access Level:</strong> <span x-text="templateDetails?.ehr_access_level"></span></div>
                        <div x-show="templateDetails?.requires_cos_approval">
                            <span class="badge bg-warning">Requires COS Approval</span>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Request Type -->
                <div class="step-section mb-4">
                    <h5 class="mb-3">
                        <span class="badge bg-primary">2</span> Request Type
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="request_type" class="form-label">
                                Request Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('request_type') is-invalid @enderror" 
                                    id="request_type" 
                                    name="request_type"
                                    required>
                                <option value="">-- Select Type --</option>
                                <option value="new_access">New Access</option>
                                <option value="additional_rights">Additional Rights</option>
                                <option value="reactivation">Account Reactivation</option>
                                <option value="termination">Account Termination</option>
                            </select>
                            @error('request_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 3: User Details -->
                <div class="step-section mb-4">
                    <h5 class="mb-3">
                        <span class="badge bg-primary">3</span> User Details
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">
                                First Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" 
                                   name="first_name"
                                   value="{{ old('first_name') }}"
                                   required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">
                                Last Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('last_name') is-invalid @enderror" 
                                   id="last_name" 
                                   name="last_name"
                                   value="{{ old('last_name') }}"
                                   required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email"
                                   value="{{ old('email') }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="payroll_number" class="form-label">Payroll Number</label>
                            <input type="text" 
                                   class="form-control @error('payroll_number') is-invalid @enderror" 
                                   id="payroll_number" 
                                   name="payroll_number"
                                   value="{{ old('payroll_number') }}">
                            @error('payroll_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" 
                                   class="form-control @error('username') is-invalid @enderror" 
                                   id="username" 
                                   name="username"
                                   value="{{ old('username') }}">
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="job_title" class="form-label">Job Title</label>
                            <input type="text" 
                                   class="form-control @error('job_title') is-invalid @enderror" 
                                   id="job_title" 
                                   name="job_title"
                                   value="{{ old('job_title') }}">
                            @error('job_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Step 4: Justification -->
                <div class="step-section mb-4">
                    <h5 class="mb-3">
                        <span class="badge bg-primary">4</span> Justification
                    </h5>
                    
                    <div class="mb-3">
                        <label for="justification" class="form-label">
                            Request Justification <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control @error('justification') is-invalid @enderror" 
                                  id="justification" 
                                  name="justification"
                                  rows="4"
                                  required
                                  placeholder="Provide a detailed justification for this access request...">{{ old('justification') }}</textarea>
                        @error('justification')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Explain why this user needs access and how it relates to their job responsibilities.</small>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between">
                    <a href="{{ route('dashboard.requester') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function requestForm() {
        return {
            selectedDepartment: '',
            selectedTemplate: '',
            templates: [],
            templateDetails: null,
            
            async loadTemplates() {
                if (!this.selectedDepartment) {
                    this.templates = [];
                    return;
                }
                
                try {
                    const response = await fetch(`/api/departments/${this.selectedDepartment}/templates`);
                    const data = await response.json();
                    this.templates = data.data.templates;
                } catch (error) {
                    console.error('Error loading templates:', error);
                }
            },
            
            async loadTemplateDetails() {
                if (!this.selectedTemplate) {
                    this.templateDetails = null;
                    return;
                }
                
                try {
                    const response = await fetch(`/api/templates/${this.selectedTemplate}`);
                    const data = await response.json();
                    this.templateDetails = data.data;
                } catch (error) {
                    console.error('Error loading template details:', error);
                }
            }
        }
    }
</script>
@endpush

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    
    .step-section {
        border-left: 3px solid var(--primary-color);
        padding-left: 1.5rem;
    }
    
    .badge {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
</style>
@endpush