<?php

namespace Modules\TenantOrgModule\Database\Seeders;

use Modules\TenantOrgModule\App\Models\BusinessCategory;
use Modules\TenantOrgModule\App\Models\BusinessSubUnit;
use Illuminate\Database\Seeder;

class BusinessCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Professional Services' => [
                'Real Estate Agencies',
                'Law Firms',
                'Accounting Firms',
                'Financial Advisors',
                'Management Consultants',
                'Insurance Brokers',
                'Architectural Firms',
                'Engineering Consultants',
            ],
            'Healthcare' => [
                'Private Medical Practices',
                'Dental Clinics',
                'Optometrists',
                'Chiropractors',
                'Physical Therapy Clinics',
                'Mental Health Counselors',
                'Specialized Clinics',
            ],
            'Education and Training' => [
                'Private Tutoring Services',
                'Test Prep Centers',
                'Professional Certification Programs',
                'Vocational Schools',
                'Language Schools',
            ],
            'Home and Personal Services' => [
                'Luxury Car Dealerships',
                'High-End Home Improvement Services',
                'Landscape Design Firms',
                'Custom Home Builders',
                'Interior Design Firms',
                'Pool Installation and Maintenance',
                'Premium Cleaning Services',
            ],
            'Technology and IT Services' => [
                'IT Consulting Firms',
                'Software Development Companies',
                'Managed IT Services',
                'Cybersecurity Firms',
                'Web Design and Development Agencies',
            ],
            'Hospitality and Travel' => [
                'Boutique Hotels',
                'Luxury Travel Agencies',
                'Event Planning Services',
                'Wedding Planning Services',
                'Vacation Rental Management Companies',
            ],
            'Financial Services' => [
                'Mortgage Brokers',
                'Investment Firms',
                'Private Equity Firms',
                'Wealth Management Firms',
            ],
            'Retail and E-commerce (High-End)' => [
                'Luxury Goods Retailers',
                'High-End Fashion Boutiques',
                'Specialty Electronics Stores',
                'Fine Jewelry Stores',
            ],
            'Health and Wellness' => [
                'High-End Gyms and Fitness Studios',
                'Personal Training Services',
                'Spa and Wellness Centers',
                'Cosmetic Surgery Clinics',
                'Nutrition and Diet Consulting Services',
            ],
            'B2B Services' => [
                'Commercial Real Estate Brokers/agents',
                'Business Consulting Firms',
                'Corporate Law Firms',
                'Marketing and Advertising Agencies',
                'Recruitment and Staffing Agencies',
            ],
            'Automotive' => [
                'Luxury Auto Dealerships',
                'Custom Car Shops',
                'Auto Leasing Companies',
            ],
            'Others' => [
                'Private Security Firms',
                'Exclusive Country Clubs',
            ],
        ];

        foreach ($categories as $categoryName => $subUnits) {
            $category = BusinessCategory::create(['name' => $categoryName]);
            foreach ($subUnits as $subUnitName) {
                BusinessSubUnit::create(['category_id' => $category->id, 'name' => $subUnitName]);
            }
        }
    }
}
