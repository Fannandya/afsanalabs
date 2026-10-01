<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\AboutTimelineItem;
use App\Models\BusinessSetting;
use App\Models\ClientLogo;
use App\Models\Faq;
use App\Models\FooterLegalLink;
use App\Models\HeroContent;
use App\Models\MockupOffer;
use App\Models\NavLink;
use App\Models\ObjectionQuestion;
use App\Models\Portfolio;
use App\Models\PricePackage;
use App\Models\ProcessStep;
use App\Models\ReferencePriceCard;
use App\Models\SectionHeader;
use App\Models\SeoSetting;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\ValueProp;

class HomeController extends Controller
{
    public function index()
    {
        $navLinks = NavLink::active()->ordered()->get();
        [$topnav, $footer] = [$navLinks->where('placement', 'topnav')->values(), $navLinks->where('placement', 'footer')->values()];

        return response()->json(['data' => [
            'businessSettings' => BusinessSetting::find(1),
            'navLinks' => ['topnav' => $topnav, 'footer' => $footer],
            'footerLegalLinks' => FooterLegalLink::active()->ordered()->get(),
            'hero' => HeroContent::find(1),
            'sectionHeaders' => SectionHeader::all()->keyBy('section_key'),
            'valueProps' => ValueProp::active()->ordered()->get(),
            'processSteps' => ProcessStep::active()->ordered()->get(),
            'objectionQuestions' => ObjectionQuestion::active()->ordered()->get(),
            'referencePriceCards' => ReferencePriceCard::active()->ordered()->get(),
            'services' => Service::active()->ordered()->get(),
            'featuredPortfolios' => Portfolio::with('category')->where('is_featured', true)->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'pricePackages' => PricePackage::active()->ordered()->get(),
            'mockupOffer' => MockupOffer::find(1),
            'faqs' => Faq::active()->ordered()->get(),
            'aboutTimeline' => AboutTimelineItem::active()->ordered()->get(),
            'teamMembers' => TeamMember::active()->ordered()->get(),
            'clientLogos' => ClientLogo::active()->ordered()->get(),
            'seoSettings' => SeoSetting::find(1),
        ]]);
    }
}
