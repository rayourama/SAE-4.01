
const competences = [];

function ajouterCompetence() {
  const skillName = document.getElementById("skillName").value.trim();
  const skillSpecialty = document.getElementById("skillSpecialty").value.trim();
  const skillLevel = document.getElementById("skillLevel").value.trim();

  if (skillName === "" || skillSpecialty === "" || skillLevel === "") {
    alert("Veuillez remplir tous les champs.");
    return;
  }

  const nouvelleCompetence = {
    skillName: skillName,
    skillSpecialty: skillSpecialty,
    skillLevel: skillLevel
  };

  competences.push(nouvelleCompetence);

  ajouterCompetenceALaListe(nouvelleCompetence);

  document.getElementById("competences").value = JSON.stringify(competences); 

  document.getElementById("skillName").value = "";
  document.getElementById("skillSpecialty").value = "";
  document.getElementById("skillLevel").value = "";


}

function ajouterCompetenceALaListe(competence) {
  const newSkillItem = document.createElement("li");
  newSkillItem.textContent = `${competence.skillName} (${competence.skillSpecialty}, niveau : ${competence.skillLevel})`;
  document.getElementById("competencesList").appendChild(newSkillItem);
}


    function annulerModification() {
        window.location.href = '?controller=profile';
    }
