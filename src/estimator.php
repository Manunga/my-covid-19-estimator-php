<?php

function covid19ImpactEstimator($data)
{
  $data = json_decode(file_get_contents('php://input'));
  $region = $data->region;
  $timeToElapse = $data->timetoElapse;
  $periodType = $data->periodType;
  $reportedCases = $data->reportedCases;
  $population = $data->population;
  $totalHospitalBeds = $data->totalHospitalBeds;
  $avgDailyIncomeInUSD = $data->region->avgDailyIncomeInUSD;

  $impactCurrentlyInfected = $data->reportedCases * 10;
  $severeImpactCurrentlyInfected = $data->reportedCases * 50;

  if ($periodType === 'days')
  {
    $timeToElapse = $timeToElapse;
    $factor = 2**int($timeToElapse/3);
    $impactInfectionsByRequestedTime = $impactCurrentlyInfected * $factor;
    $severeImpactInfectionByRequestedTime = $severeImpactCurrentlyInfected * $factor;
  }
  elseif ($timeToElapse === 'weeks') {
    $timeToElapse = $timeToElapse * 7;
    $factor = 2**int($timeToElapse/3);
    $impactInfectionsByRequestedTime = $impactCurrentlyInfected * $factor;
    $severeImpactInfectionByRequestedTime = $severeImpactCurrentlyInfected * $factor;
  }
  elseif ($timeToElapse === 'months') {
    $timeToElapse = $timeToElapse * 30;
    $factor = 2**int($timeToElapse/3);
    $impactInfectionsByRequestedTime = $impactCurrentlyInfected * $factor;
    $severeImpactInfectionByRequestedTime = $severeImpactCurrentlyInfected * $factor;
  }
  else {
    return "Period type must be in days, weeks or months";
  }

  $impactSevereCasesByRequestedTime = int(0.15 * $impactInfectionsByRequestedTime);
  $severeImpactSevereCasesByRequestedTime = int(0.15 * $severeImpactInfectionByRequestedTime);

  $impactHospitalBedsByRequestedTime = int((0.35 * $totalHospitalBeds) - $impactSevereCasesByRequestedTime);
  $severeImpactHospitalBedsByRequestedTime = int((0.35 * $totalHospitalBeds) - $severeImpactSevereCasesByRequestedTime);

  $impactCasesForICUByRequestedTime = int(0.05 * $impactInfectionsByRequestedTime);
  $severeImpactCasesForICUByRequestedTime = int(0.05 * $severeImpactInfectionByRequestedTime);

  $impactCasesForVentilatorsByRequestedTime = int(0.02 * $impactInfectionsByRequestedTime);
  $severeImpactCasesForVentilatorsByRequestedTime = int(0.02 * $severeImpactInfectionByRequestedTime);

  $impactDollarsInFlight = floor(($impactInfectionsByRequestedTime * 0.65 * $avgDailyIncomeInUSD * 30), 2);
  $severeImpactDollarsInFlight = floor(($severeImpactInfectionByRequestedTime * 0.65 * $avgDailyIncomeInUSD * 30), 2);

  $response = array (
    "data" => $decodeData,
    "imapact" => array(
      'currentlyInfected' => $impactCurrentlyInfected,
      'infectionsByRequestedTime' => $impactInfectionsByRequestedTime,
      'severeCasesByRequestedTime' => $impactSevereCasesByRequestedTime,
      'hospitalBedsByRequestedTime' => $impactHospitalBedsByRequestedTime,
      'casesForICUByRequestedTime' => $impactCasesForICUByRequestedTime,
      'casesForVentilatorsByRequestedTime' => $impactCasesForVentilatorsByRequestedTime,
      'dollarsInFlight' => $impactDollarsInFlight
    ), 
    "severeImpact" => array(
      'currentlyInfected' => $severeImpactCurrentlyInfected,
      'infectionsByRequestedTime' => $severeImpactInfectionByRequestedTime,
      'severeCasesByRequestedTime' => $severeImpactSevereCasesByRequestedTime,
      'hospitalsBedsByRequestedTime' => $severeImpactHospitalBedsByRequestedTime,
      'casesForICUByRequestedTime' => $severeImpactCasesForICUByRequestedTime,
      'casesForVentilatorsByRequestedTime' => $severeImpactCasesForVentilatorsByRequestedTime,
      'dollarsInFlight' => $severeImpactDollarsInFlight
    )
  );

  $data = json_encode($response);

  return $data;
}